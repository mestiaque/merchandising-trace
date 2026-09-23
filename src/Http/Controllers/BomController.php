<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\BomRequest;
use ME\MerchandisingTrace\Models\Bom;
use ME\MerchandisingTrace\Models\Style;
use ME\MerchandisingTrace\Services\DocumentNumberService;

class BomController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_bom.list');

        $boms = Bom::query()
            ->with(['style'])
            ->when($request->filled('search'), fn ($q) => $q->where('bom_no', 'like', '%' . $request->search . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.boms.index', ['boms' => $boms]);
    }

    public function create(): View
    {
        $this->authorize('merch_bom.add');

        return view('merchandising-trace::admin.boms.create', $this->formOptions());
    }

    public function store(BomRequest $request, DocumentNumberService $numbers): RedirectResponse
    {
        $data = $request->validated();

        $version = Bom::where('style_id', $data['style_id'])->max('version');

        $bom = Bom::create([
            'bom_no' => $numbers->next(Bom::class, 'bom_no', 'BOM'),
            'style_id' => $data['style_id'],
            'version' => ($version ?? 0) + 1,
            'status' => 'draft',
            'remarks' => $data['remarks'] ?? null,
            'bom_file' => $request->hasFile('bom_file') ? $request->file('bom_file')->store('merchandising-trace/bom-files', 'public') : null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('merchandising-trace.boms.show', $bom)->with('success', "BOM {$bom->bom_no} created successfully.");
    }

    public function show(Bom $bom): View
    {
        $this->authorize('merch_bom.view');

        $bom->load(['style.buyer', 'approver']);

        return view('merchandising-trace::admin.boms.show', compact('bom'));
    }

    public function edit(Bom $bom): View
    {
        $this->authorize('merch_bom.edit');

        return view('merchandising-trace::admin.boms.edit', ['bom' => $bom] + $this->formOptions());
    }

    public function update(BomRequest $request, Bom $bom): RedirectResponse
    {
        $data = $request->validated();

        $update = ['remarks' => $data['remarks'] ?? null];
        if ($request->hasFile('bom_file')) {
            $update['bom_file'] = $request->file('bom_file')->store('merchandising-trace/bom-files', 'public');
        }

        $bom->update($update);

        return redirect()->route('merchandising-trace.boms.show', $bom)->with('success', 'BOM updated successfully.');
    }

    public function destroy(Bom $bom): RedirectResponse
    {
        $this->authorize('merch_bom.delete');

        $bom->delete();

        return redirect()->route('merchandising-trace.boms.index')->with('success', 'BOM deleted successfully.');
    }

    public function approve(Bom $bom): RedirectResponse
    {
        $this->authorize('merch_bom.edit');

        $bom->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', "BOM {$bom->bom_no} approved.");
    }

    public function viewFile(Bom $bom)
    {
        $this->authorize('merch_bom.view');

        abort_unless($bom->bom_file, 404);

        return Storage::disk('public')->response($bom->bom_file);
    }

    public function downloadFile(Bom $bom)
    {
        $this->authorize('merch_bom.view');

        abort_unless($bom->bom_file, 404);

        return Storage::disk('public')->download($bom->bom_file);
    }

    private function formOptions(): array
    {
        return [
            'stylesOptions' => Style::query()->active()->orderBy('name')->get(),
        ];
    }
}
