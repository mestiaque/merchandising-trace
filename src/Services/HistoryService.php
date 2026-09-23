<?php

namespace ME\MerchandisingTrace\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Bridge\InvFinishedGoodsReceive;
use ME\MerchandisingTrace\Models\Bridge\InvGrn;
use ME\MerchandisingTrace\Models\Bridge\InvIssue;
use ME\MerchandisingTrace\Models\Bridge\InvProductionConsumption;
use ME\MerchandisingTrace\Models\Bridge\InvRequisition;
use ME\MerchandisingTrace\Models\Bridge\TrcBuyerInspection;
use ME\MerchandisingTrace\Models\Bridge\TrcCutting;
use ME\MerchandisingTrace\Models\Bridge\TrcFabricIssue;
use ME\MerchandisingTrace\Models\Bridge\TrcGarmentProcessJob;
use ME\MerchandisingTrace\Models\Bridge\TrcInternalFinalInspection;
use ME\MerchandisingTrace\Models\Bridge\TrcPackingList;
use ME\MerchandisingTrace\Models\Bridge\TrcPlanLine;
use ME\MerchandisingTrace\Models\Bridge\TrcQualityAction;
use ME\MerchandisingTrace\Models\Bridge\TrcShipment;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\ProductionHandover;
use ME\MerchandisingTrace\Models\Sample;
use ME\MerchandisingTrace\Models\SalesContractPo;
use ME\MerchandisingTrace\Models\Style;

/**
 * Cross-module "360° History" — merges events from Merchandising
 * (this package), Production (production-trace, via Bridge models keyed
 * on trc_plan_lines.merch_order_id), and Inventory (sfl-inventory, via
 * Bridge models keyed on their mer_style_id/mer_sales_contract_po_id/
 * mer_buyer_id soft-reference columns) into one chronological timeline
 * for a given PO, Style, or Buyer.
 *
 * Production and Inventory tables are read defensively (Schema::hasTable
 * / hasColumn) since those sibling packages/columns may not be installed
 * or migrated in every environment.
 */
class HistoryService
{
    public function search(string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return ['pos' => collect(), 'styles' => collect(), 'buyers' => collect()];
        }

        return [
            'pos' => SalesContractPo::query()
                ->with(['style', 'salesContract.buyer'])
                ->where('po_no', 'like', "%{$q}%")
                ->latest('id')->limit(20)->get(),
            'styles' => Style::query()
                ->with('buyer')
                ->where('style_no', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")
                ->latest('id')->limit(20)->get(),
            'buyers' => Buyer::query()
                ->where('name', 'like', "%{$q}%")
                ->orWhere('code', 'like', "%{$q}%")
                ->orderBy('name')->limit(20)->get(),
        ];
    }

    public function timelineForPo(SalesContractPo $po): Collection
    {
        $events = collect();

        $events->push($this->event($po->created_at, 'Merchandising', 'fa-file-signature', "PO {$po->po_no} created", $po->style->style_no ?? null));

        foreach ($po->revisions as $rev) {
            $events->push($this->event($rev->changed_at, 'Merchandising', 'fa-pen', ucfirst(str_replace('_', ' ', $rev->field)) . ' revised', "{$rev->old_value} → {$rev->new_value}" . ($rev->reason ? " ({$rev->reason})" : '')));
        }

        foreach ($po->tnaPlan?->tasks ?? [] as $task) {
            if ($task->actual_date) {
                $events->push($this->event($task->actual_date, 'Merchandising', 'fa-calendar-check', "{$task->group_name}: {$task->task_name}", ucfirst(str_replace('_', ' ', $task->status))));
            }
        }

        foreach (Sample::query()->where('style_id', $po->style_id)->get() as $sample) {
            $type = $sample->sampleType->name ?? 'Sample';
            if ($sample->request_date) {
                $events->push($this->event($sample->request_date, 'Merchandising', 'fa-vial', "{$type} requested", $sample->sample_no));
            }
            if ($sample->submit_date) {
                $events->push($this->event($sample->submit_date, 'Merchandising', 'fa-vial-circle-check', "{$type} submitted to buyer", $sample->sample_no));
            }
            if ($sample->approval_date) {
                $events->push($this->event($sample->approval_date, 'Merchandising', 'fa-vial-circle-check', "{$type} " . $sample->status, $sample->sample_no));
            }
        }

        foreach (MaterialBooking::query()->where('sales_contract_po_id', $po->id)->get() as $mb) {
            $label = ucfirst($mb->type) . " booking {$mb->booking_no}";
            if ($mb->booking_date) {
                $events->push($this->event($mb->booking_date, 'Merchandising', 'fa-boxes-packing', $label . ' booked', $mb->supplier->name ?? null));
            }
            if ($mb->pi_date) {
                $events->push($this->event($mb->pi_date, 'Merchandising', 'fa-file-invoice-dollar', "{$label} — PI issued", $mb->pi_no));
            }
            if ($mb->lc_date) {
                $events->push($this->event($mb->lc_date, 'Merchandising', 'fa-file-contract', "{$label} — LC opened", $mb->lc_no));
            }
            if ($mb->x_mill_date) {
                $events->push($this->event($mb->x_mill_date, 'Merchandising', 'fa-industry', "{$label} — ex-mill", null));
            }
            foreach ($mb->consignments as $c) {
                if ($c->actual_date) {
                    $events->push($this->event($c->actual_date, 'Merchandising', 'fa-truck-ramp-box', "{$label} — consignment #{$c->consignment_no} received", "{$c->received_qty} received"));
                }
            }
            foreach ($mb->receipts as $r) {
                $events->push($this->event($r->receive_date, 'Merchandising', 'fa-warehouse', "{$label} — item received", ($r->item->name ?? '') . " qty {$r->qty}"));
            }
        }

        foreach (ProductionHandover::query()->where('sales_contract_po_id', $po->id)->get() as $h) {
            $events->push($this->event($h->handover_date, 'Merchandising', 'fa-right-left', $h->status === 'handed_over' ? 'Handed over to production' : 'Handover rolled back', $h->rollback_reason ?? $h->override_reason));
        }

        $this->addProductionEvents($events, $po->id);
        $this->addInventoryEvents($events, styleId: $po->style_id, poId: $po->id, buyerId: null);

        return $this->sorted($events);
    }

    public function timelineForStyle(Style $style): Collection
    {
        $events = collect();

        $events->push($this->event($style->created_at, 'Merchandising', 'fa-shirt', "Style {$style->style_no} created", $style->name));

        foreach (Bom::query()->where('style_id', $style->id)->get() as $bom) {
            if ($bom->approved_at) {
                $events->push($this->event($bom->approved_at, 'Merchandising', 'fa-list-check', "BOM {$bom->bom_no} approved", "v{$bom->version}"));
            }
        }

        foreach (SalesContractPo::query()->where('style_id', $style->id)->get() as $po) {
            $events = $events->merge($this->timelineForPo($po));
        }

        return $this->sorted($events);
    }

    private function addProductionEvents(Collection $events, int $poId): void
    {
        if (! Schema::hasTable('trc_plan_lines') || ! Schema::hasColumn('trc_plan_lines', 'merch_order_id')) {
            return;
        }

        $line = TrcPlanLine::query()->where('merch_order_id', $poId)->first();
        if (! $line) {
            return;
        }

        $milestones = [
            'pp_meeting_date' => ['fa-people-arrows', 'PP meeting'],
            'plan_cut_date' => ['fa-scissors', 'Cutting started (planned)'],
            'plan_cut_close_date' => ['fa-scissors', 'Cutting closed (planned)'],
            'sewing_start_date' => ['fa-user-ninja', 'Sewing started (planned)'],
            'sewing_close_date' => ['fa-user-ninja', 'Sewing closed (planned)'],
            'finishing_start_date' => ['fa-broom', 'Finishing started (planned)'],
            'finishing_close_date' => ['fa-broom', 'Finishing closed (planned)'],
            'fri_date' => ['fa-flag-checkered', 'FRI (planned)'],
            'shipment_date' => ['fa-ship', 'Shipment (planned)'],
        ];
        foreach ($milestones as $field => [$icon, $label]) {
            if ($line->{$field}) {
                $events->push($this->event($line->{$field}, 'Production', $icon, $label, "Status: " . str_replace('_', ' ', $line->status)));
            }
        }

        if (Schema::hasTable('trc_fabric_issues')) {
            foreach (TrcFabricIssue::query()->where('plan_line_id', $line->id)->get() as $fi) {
                $events->push($this->event($fi->issue_date, 'Production', 'fa-scroll', "Fabric issued ({$fi->issue_no})", ucfirst($fi->status)));
            }
        }
        if (Schema::hasTable('trc_cuttings')) {
            foreach (TrcCutting::query()->where('plan_line_id', $line->id)->get() as $c) {
                $events->push($this->event($c->cut_date, 'Production', 'fa-scissors', "Cutting ({$c->cut_no})", ucfirst($c->status)));
            }
        }
        if (Schema::hasTable('trc_garment_process_jobs')) {
            foreach (TrcGarmentProcessJob::query()->where('plan_line_id', $line->id)->get() as $j) {
                $events->push($this->event($j->issue_date, 'Production', 'fa-soap', ucfirst($j->process_type) . " job issued ({$j->job_no})", ucfirst($j->status)));
                if ($j->receive_date) {
                    $events->push($this->event($j->receive_date, 'Production', 'fa-soap', ucfirst($j->process_type) . " job received ({$j->job_no})", ucfirst($j->status)));
                }
            }
        }
        if (Schema::hasTable('trc_internal_final_inspections')) {
            foreach (TrcInternalFinalInspection::query()->where('plan_line_id', $line->id)->get() as $insp) {
                $events->push($this->event($insp->inspection_date, 'Production', 'fa-magnifying-glass', "Internal final inspection ({$insp->inspection_no})", strtoupper($insp->result) . " — released {$insp->released_qty}"));
            }
        }
        if (Schema::hasTable('trc_buyer_inspections')) {
            foreach (TrcBuyerInspection::query()->where('plan_line_id', $line->id)->get() as $insp) {
                $events->push($this->event($insp->inspection_date, 'Production', 'fa-user-check', ucfirst($insp->inspection_type) . " buyer inspection ({$insp->inspection_no})", ucfirst($insp->result)));
            }
        }
        if (Schema::hasTable('trc_packing_lists')) {
            foreach (TrcPackingList::query()->where('plan_line_id', $line->id)->get() as $pl) {
                $events->push($this->event($pl->pack_date, 'Production', 'fa-box', "Packing list ({$pl->pl_no})", "{$pl->total_cartons} cartons / {$pl->total_qty} pcs — " . ucfirst($pl->status)));
            }
        }
        if (Schema::hasTable('trc_shipments')) {
            foreach (TrcShipment::query()->where('plan_line_id', $line->id)->get() as $sh) {
                $events->push($this->event($sh->shipment_date ?? $sh->ex_factory_date, 'Production', 'fa-ship', "Shipment ({$sh->invoice_no})", ucfirst($sh->status)));
            }
        }
        if (Schema::hasTable('trc_quality_actions')) {
            foreach (TrcQualityAction::query()->where('plan_line_id', $line->id)->get() as $qa) {
                $events->push($this->event($qa->date, 'Production', 'fa-triangle-exclamation', "Quality action — {$qa->process}", ucfirst($qa->status) . ($qa->root_cause ? ": {$qa->root_cause}" : '')));
            }
        }
    }

    private function addInventoryEvents(Collection $events, ?int $styleId, ?int $poId, ?int $buyerId): void
    {
        $match = function ($query) use ($styleId, $poId, $buyerId) {
            return $query
                ->when($poId, fn ($q) => $q->orWhere('mer_sales_contract_po_id', $poId))
                ->when($styleId, fn ($q) => $q->orWhere('mer_style_id', $styleId))
                ->when($buyerId, fn ($q) => $q->orWhere('mer_buyer_id', $buyerId));
        };

        if ($this->invReady('inv_requisitions')) {
            foreach (InvRequisition::query()->where(fn ($q) => $match($q))->get() as $r) {
                $events->push($this->event($r->requisition_date, 'Inventory', 'fa-clipboard-list', "Requisition {$r->requisition_no}", ucfirst(str_replace('_', ' ', $r->status))));
            }
        }
        if ($this->invReady('inv_issues')) {
            foreach (InvIssue::query()->where(fn ($q) => $match($q))->get() as $i) {
                $events->push($this->event($i->issue_date, 'Inventory', 'fa-dolly', "Material issued ({$i->issue_no})", null));
            }
        }
        if ($this->invReady('inv_production_consumptions')) {
            foreach (InvProductionConsumption::query()->where(fn ($q) => $match($q))->get() as $c) {
                $events->push($this->event($c->consumption_date, 'Inventory', 'fa-industry', "Production consumption ({$c->consumption_no})", null));
            }
        }
        if ($this->invReady('inv_finished_goods_receives')) {
            foreach (InvFinishedGoodsReceive::query()->where(fn ($q) => $match($q))->get() as $fg) {
                $events->push($this->event($fg->receive_date, 'Inventory', 'fa-boxes-stacked', "Finished goods received ({$fg->receive_no})", null));
            }
        }
        if ($this->invReady('inv_grns')) {
            foreach (InvGrn::query()->where(fn ($q) => $match($q))->get() as $g) {
                $events->push($this->event($g->receive_date, 'Inventory', 'fa-truck-loading', "GRN {$g->grn_number}", ucfirst($g->status)));
            }
        }
    }

    private function invReady(string $table): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, 'mer_style_id');
    }

    private function event($date, string $module, string $icon, string $title, ?string $detail = null): array
    {
        return [
            'date' => $date ? \Illuminate\Support\Carbon::parse($date) : null,
            'module' => $module,
            'icon' => $icon,
            'title' => $title,
            'detail' => $detail,
        ];
    }

    private function sorted(Collection $events): Collection
    {
        return $events->filter(fn ($e) => $e['date'] !== null)->sortByDesc('date')->values();
    }
}
