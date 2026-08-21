<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\SeasonRequest;
use ME\MerchandisingTrace\Models\Season;

class SeasonController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_season.list');

        $seasons = Season::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.seasons.index', ['seasons' => $seasons]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_season.list');

        $seasons = Season::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Seasons',
            'columns' => ['name' => 'Name', 'code' => 'Code', 'start_date' => 'Start Date', 'end_date' => 'End Date'],
            'rows'    => $seasons,
        ]);
    }

    public function store(SeasonRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Season::create($data);

        return back()->with('success', 'Season created successfully.');
    }

    public function update(SeasonRequest $request, Season $season): RedirectResponse
    {
        $season->update($request->validated());

        return back()->with('success', 'Season updated successfully.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        $this->authorize('merch_season.delete');

        $season->delete();

        return back()->with('success', 'Season deleted successfully.');
    }
}
