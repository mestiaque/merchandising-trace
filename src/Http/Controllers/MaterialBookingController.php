<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\MaterialBookingRequest;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Color;
use ME\MerchandisingTrace\Models\Currency;
use ME\MerchandisingTrace\Models\Item;
use ME\MerchandisingTrace\Models\MaterialBooking;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Models\Supplier;
use ME\MerchandisingTrace\Models\Uom;
use ME\MerchandisingTrace\Services\DocumentNumberService;
use ME\MerchandisingTrace\Services\MaterialBookingTnaSyncService;

class MaterialBookingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_material_booking.list');

        $bookings = MaterialBooking::query()
            ->with(['style', 'supplier'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type), fn ($q) => $q->where('type', 'fabric'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.material-bookings.index', [
            'bookings' => $bookings,
            'activeType' => $request->input('type', 'fabric'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('merch_material_booking.add');

        return view('merchandising-trace::admin.material-bookings.create', $this->formOptions());
    }

    /**
     * §M05 AC: "Create Booking from BOM" — turns an approved BOM into a
     * draft booking covering every line, with no manual qty re-entry. A BOM
     * is per-style (created before order confirmation, per the build's own
     * flow diagram), so it carries no sales_contract_id of its own — the
     * confirmed contract for that style must be picked explicitly here.
     */
    public function createFromBom(Request $request, Bom $bom): RedirectResponse
    {
        $this->authorize('merch_material_booking.add');

        if ($bom->status !== 'approved') {
            return back()->with('error', 'Only an approved BOM can be turned into a booking.');
        }

        $data = $request->validate([
            'sales_contract_id' => ['required', 'integer', 'exists:mer_sales_contracts,id'],
        ]);

        $numbers = app(DocumentNumberService::class);
        $booking = DB::transaction(function () use ($bom, $data, $numbers) {
            $firstItem = $bom->items->first();

            $booking = MaterialBooking::create([
                'booking_no' => $numbers->next(MaterialBooking::class, 'booking_no', 'MB'),
                'type' => $firstItem->item_type ?? 'trims',
                'sales_contract_id' => $data['sales_contract_id'],
                'style_id' => $bom->style_id,
                'supplier_id' => $firstItem->supplier_id ?? null,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($bom->items as $line) {
                $booking->items()->create([
                    'item_id' => $line->item_id,
                    'color_id' => $line->color_id,
                    'description' => $line->part_name,
                    'booked_qty' => $line->consumption,
                    'uom_id' => $line->uom_id,
                    'rate' => $line->rate,
                ]);
            }

            return $booking;
        });

        return redirect()->route('merchandising-trace.material-bookings.show', $booking)->with('success', "Booking {$booking->booking_no} created from BOM {$bom->bom_no}.");
    }

    public function store(MaterialBookingRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? [];
        unset($data['items']);
        $data['booking_no'] = $numbers->next(MaterialBooking::class, 'booking_no', 'MB');
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();

        $booking = DB::transaction(function () use ($data, $items) {
            $booking = MaterialBooking::create($data);
            foreach ($items as $line) {
                if (empty($line['item_id'])) {
                    continue;
                }
                $booking->items()->create($line);
            }

            return $booking;
        });

        return redirect()->route('merchandising-trace.material-bookings.show', $booking)->with('success', "Booking {$booking->booking_no} created successfully.");
    }

    public function show(MaterialBooking $materialBooking): View
    {
        $this->authorize('merch_material_booking.view');

        $materialBooking->load(['style', 'supplier', 'currency', 'items.item', 'consignments', 'receipts.item']);

        return view('merchandising-trace::admin.material-bookings.show', [
            'booking' => $materialBooking,
            'itemsOptions' => Item::query()->active()->orderBy('name')->get(),
        ]);
    }

    /**
     * PI / LC / X-mill entry — a focused update that also fires the T&A
     * sync (§8.4/§4.9 AC).
     */
    public function updateDates(Request $request, MaterialBooking $materialBooking, MaterialBookingTnaSyncService $sync): RedirectResponse
    {
        $this->authorize('merch_material_booking.edit');

        $data = $request->validate([
            'booking_date' => ['nullable', 'date'],
            'pi_no' => ['nullable', 'string', 'max:150'],
            'pi_date' => ['nullable', 'date'],
            'lc_no' => ['nullable', 'string', 'max:150'],
            'lc_date' => ['nullable', 'date'],
            'x_mill_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:' . implode(',', MaterialBooking::STATUSES)],
        ]);

        $materialBooking->update($data);
        $synced = $sync->syncFromBooking($materialBooking);

        return back()->with('success', "Booking updated." . ($synced ? " {$synced} T&A task(s) auto-updated." : ''));
    }

    public function addConsignment(Request $request, MaterialBooking $materialBooking): RedirectResponse
    {
        $this->authorize('merch_material_booking.edit');

        $data = $request->validate([
            'consignment_no' => ['required', 'integer', 'min:1', 'max:10'],
            'planned_date' => ['nullable', 'date'],
            'planned_qty' => ['nullable', 'numeric', 'min:0'],
        ]);

        $materialBooking->consignments()->updateOrCreate(['consignment_no' => $data['consignment_no']], $data);

        return back()->with('success', 'Consignment schedule saved.');
    }

    public function receiveConsignment(Request $request, MaterialBooking $materialBooking, \ME\MerchandisingTrace\Models\MaterialConsignment $consignment, MaterialBookingTnaSyncService $sync): RedirectResponse
    {
        $this->authorize('merch_material_booking.edit');

        $data = $request->validate([
            'actual_date' => ['required', 'date'],
            'received_qty' => ['required', 'numeric', 'min:0'],
            'challan_no' => ['nullable', 'string', 'max:150'],
            'invoice_no' => ['nullable', 'string', 'max:150'],
        ]);

        $data['status'] = $data['received_qty'] < $consignment->planned_qty ? 'short' : 'received';
        $consignment->update($data);

        $synced = $sync->syncFromConsignment($consignment);

        if ($materialBooking->consignments()->where('status', '!=', 'received')->doesntExist()) {
            $materialBooking->update(['status' => 'received']);
        } else {
            $materialBooking->update(['status' => 'partial_received']);
        }

        return back()->with('success', "Consignment received." . ($synced ? " {$synced} T&A task(s) auto-updated." : ''));
    }

    /**
     * §M10 AC: every receipt fires the T&A auto-sync.
     */
    public function receiveItem(Request $request, MaterialBooking $materialBooking, MaterialBookingTnaSyncService $sync): RedirectResponse
    {
        $this->authorize('merch_material_booking.edit');

        $data = $request->validate([
            'item_id' => ['required', 'integer', 'exists:mer_items,id'],
            'receive_date' => ['required', 'date'],
            'qty' => ['required', 'numeric', 'min:0'],
            'store_ref' => ['nullable', 'string', 'max:150'],
        ]);

        $receipt = $materialBooking->receipts()->create($data + ['received_by' => auth()->id()]);

        $synced = $sync->syncFromReceipt($receipt);

        if ($materialBooking->balanceQty() <= 0) {
            $materialBooking->update(['status' => 'received']);
        } elseif ($materialBooking->totalReceivedQty() > 0) {
            $materialBooking->update(['status' => 'partial_received']);
        }

        return back()->with('success', "Receipt recorded." . ($synced ? " {$synced} T&A task(s) auto-updated." : ''));
    }

    private function formOptions(): array
    {
        return [
            'salesContractsOptions' => SalesContract::query()->with('buyer')->latest('id')->limit(200)->get(),
            'stylesOptions' => Style::query()->active()->orderBy('name')->get(),
            'suppliersOptions' => Supplier::query()->active()->orderBy('name')->get(),
            'currenciesOptions' => Currency::query()->active()->orderBy('code')->get(),
            'itemsOptions' => Item::query()->active()->orderBy('name')->get(),
            'colorsOptions' => Color::query()->active()->orderBy('name')->get(),
            'uomsOptions' => Uom::query()->active()->orderBy('name')->get(),
        ];
    }
}
