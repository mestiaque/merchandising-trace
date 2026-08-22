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
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.departments.index', ['departments' => $departments]);
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated());

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
