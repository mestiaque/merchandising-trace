<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\DepartmentRequest;
use ME\MerchandisingTrace\Models\Department;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_department.list');

        $departments = Department::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.departments.index', ['departments' => $departments]);
    }

    public function print(Request $request): View
    {
        $this->authorize('merch_department.list');

        $departments = Department::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', '%' . $request->search . '%')->orWhere('code', 'like', '%' . $request->search . '%')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest('id')->get();

        return view('merchandising-trace::admin.partials.print-table', [
            'title'   => 'Departments',
            'columns' => ['name' => 'Name', 'code' => 'Code'],
            'rows'    => $departments,
        ]);
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        Department::create($data);

        return back()->with('success', 'Department created successfully.');
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('merch_department.delete');

        $department->delete();

        return back()->with('success', 'Department deleted successfully.');
    }
}
