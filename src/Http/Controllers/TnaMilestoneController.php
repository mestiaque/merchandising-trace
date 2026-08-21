<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\TnaMilestoneRequest;
use ME\MerchandisingTrace\Models\Order;
use ME\MerchandisingTrace\Models\TnaMilestone;

class TnaMilestoneController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_tna.list');

        $tna_milestones = TnaMilestone::query()
            ->with(['order'])
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->order_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->boolean('delayed_only'), fn ($q) => $q->delayed())
            ->orderBy('planned_date')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.tna-milestones.index', ['tna_milestones' => $tna_milestones] + $this->formOptions());
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_tna.list');

        $tna_milestones = TnaMilestone::query()
            ->with(['order'])
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->order_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('planned_date')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'TNA Milestones',
            'columns' => ['order.po_number' => 'Order', 'milestone_name' => 'Milestone', 'planned_date' => 'Planned Date', 'actual_date' => 'Actual Date', 'status' => 'Status'],
            'rows'    => $tna_milestones,
        ]);
    }

    public function store(TnaMilestoneRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        TnaMilestone::create($data);

        return back()->with('success', 'TNA milestone created successfully.');
    }

    public function update(TnaMilestoneRequest $request, TnaMilestone $tna_milestone): RedirectResponse
    {
        $tna_milestone->update($request->validated());

        return back()->with('success', 'TNA milestone updated successfully.');
    }

    public function destroy(TnaMilestone $tna_milestone): RedirectResponse
    {
        $this->authorize('merch_tna.delete');

        $tna_milestone->delete();

        return back()->with('success', 'TNA milestone deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'ordersOptions' => Order::query()->orderByDesc('id')->limit(200)->get(),
        ];
    }
}
