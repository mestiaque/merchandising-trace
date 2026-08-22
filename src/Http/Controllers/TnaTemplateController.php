<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\TnaTemplateTaskRequest;
use ME\MerchandisingTrace\Models\Buyer;
use ME\MerchandisingTrace\Models\Department;
use ME\MerchandisingTrace\Models\ProductType;
use ME\MerchandisingTrace\Models\TnaTemplate;
use ME\MerchandisingTrace\Models\TnaTemplateTask;

class TnaTemplateController extends Controller
{
    public function index(): View
    {
        $this->authorize('merch_tna.list');

        $templates = TnaTemplate::withCount('tasks')->orderByDesc('is_default')->get();

        return view('merchandising-trace::admin.tna-templates.index', ['templates' => $templates]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('merch_tna.add');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:mer_tna_templates,code'],
            'name' => ['required', 'string', 'max:150'],
            'buyer_id' => ['nullable', 'integer', 'exists:mer_buyers,id'],
            'product_type_id' => ['nullable', 'integer', 'exists:mer_product_types,id'],
            'anchor' => ['required', 'string', 'in:shipment,pcd,order_confirm'],
        ]);

        TnaTemplate::create($data);

        return back()->with('success', 'Template created.');
    }

    /**
     * "Clone a template per buyer/product" (§8.1) — copies every task row.
     */
    public function clone(Request $request, TnaTemplate $tnaTemplate): RedirectResponse
    {
        $this->authorize('merch_tna.add');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:mer_tna_templates,code'],
            'name' => ['required', 'string', 'max:150'],
            'buyer_id' => ['nullable', 'integer', 'exists:mer_buyers,id'],
            'product_type_id' => ['nullable', 'integer', 'exists:mer_product_types,id'],
        ]);

        $clone = TnaTemplate::create($data + ['anchor' => $tnaTemplate->anchor, 'is_active' => true]);

        foreach ($tnaTemplate->tasks as $task) {
            $clone->tasks()->create($task->only([
                'group_name', 'task_code', 'task_name', 'value_type', 'sequence', 'offset_days',
                'anchor_field', 'responsible_dept_id', 'is_mandatory', 'blocks_pcd', 'auto_source', 'auto_source_ref',
            ]));
        }

        return redirect()->route('merchandising-trace.tna-templates.show', $clone)->with('success', "Template cloned as {$clone->name}.");
    }

    public function show(TnaTemplate $tnaTemplate): View
    {
        $this->authorize('merch_tna.view');

        $tnaTemplate->load('tasks.department');

        return view('merchandising-trace::admin.tna-templates.show', [
            'tnaTemplate' => $tnaTemplate,
            'departmentsOptions' => Department::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function storeTask(TnaTemplateTaskRequest $request, TnaTemplate $tnaTemplate): RedirectResponse
    {
        $data = $request->validated();
        $data['sequence'] = $data['sequence'] ?? ($tnaTemplate->tasks()->max('sequence') + 1);
        $tnaTemplate->tasks()->create($data);

        return back()->with('success', 'Task added.');
    }

    public function updateTask(TnaTemplateTaskRequest $request, TnaTemplate $tnaTemplate, TnaTemplateTask $task): RedirectResponse
    {
        $task->update($request->validated());

        return back()->with('success', 'Task updated.');
    }

    public function destroyTask(TnaTemplate $tnaTemplate, TnaTemplateTask $task): RedirectResponse
    {
        $this->authorize('merch_tna.edit');

        $task->delete();

        return back()->with('success', 'Task removed.');
    }
}
