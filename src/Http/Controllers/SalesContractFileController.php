<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ME\MerchandisingTrace\Models\SalesContract;
use ME\MerchandisingTrace\Models\SalesContractFile;

class SalesContractFileController extends Controller
{
    public function store(Request $request, SalesContract $salesContract): RedirectResponse
    {
        $this->authorize('merch_sales_contract.edit');

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:20480'],
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('merchandising-trace/sales-contract-files', 'public');

            $salesContract->files()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        return back()->with('success', 'File(s) uploaded.');
    }

    public function view(SalesContract $salesContract, SalesContractFile $file)
    {
        $this->authorize('merch_sales_contract.view');

        abort_unless($file->sales_contract_id === $salesContract->id, 404);

        return Storage::disk('public')->response($file->path, $file->original_name);
    }

    public function download(SalesContract $salesContract, SalesContractFile $file)
    {
        $this->authorize('merch_sales_contract.view');

        abort_unless($file->sales_contract_id === $salesContract->id, 404);

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    public function destroy(SalesContract $salesContract, SalesContractFile $file): RedirectResponse
    {
        $this->authorize('merch_sales_contract.edit');

        abort_unless($file->sales_contract_id === $salesContract->id, 404);
        $file->delete();

        return back()->with('success', 'File removed.');
    }
}
