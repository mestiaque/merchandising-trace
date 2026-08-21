<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\PortRequest;
use ME\MerchandisingTrace\Models\Port;

class PortController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_port.list');

        $ports = Port::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.ports.index', ['ports' => $ports]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_port.list');

        $ports = Port::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Ports',
            'columns' => ['name' => 'Name', 'code' => 'Code', 'country' => 'Country', 'port_type' => 'Type'],
            'rows'    => $ports,
        ]);
    }

    public function store(PortRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Port::create($data);

        return back()->with('success', 'Port created successfully.');
    }

    public function update(PortRequest $request, Port $port): RedirectResponse
    {
        $port->update($request->validated());

        return back()->with('success', 'Port updated successfully.');
    }

    public function destroy(Port $port): RedirectResponse
    {
        $this->authorize('merch_port.delete');

        $port->delete();

        return back()->with('success', 'Port deleted successfully.');
    }
}
