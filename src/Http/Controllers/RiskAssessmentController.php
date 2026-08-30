<?php

namespace ME\MerchandisingTrace\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ME\MerchandisingTrace\Http\Requests\RiskAssessmentRequest;
use ME\MerchandisingTrace\Models\Factory;
use ME\MerchandisingTrace\Models\RiskAssessment;
use ME\MerchandisingTrace\Models\Season;
use ME\MerchandisingTrace\Models\Style;

class RiskAssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('merch_risk_assessment.list');

        $riskAssessments = RiskAssessment::query()
            ->with(['style', 'season'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('style', fn ($s) => $s->where('style_no', 'like', '%' . $request->search . '%')->orWhere('name', 'like', '%' . $request->search . '%')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('merchandising-trace::admin.risk-assessments.index', [
            'riskAssessments' => $riskAssessments,
        ] + $this->formOptions());
    }

    public function store(RiskAssessmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        RiskAssessment::create($data);

        return back()->with('success', 'Risk assessment created successfully.');
    }

    public function update(RiskAssessmentRequest $request, RiskAssessment $riskAssessment): RedirectResponse
    {
        $riskAssessment->update($request->validated());

        return back()->with('success', 'Risk assessment updated successfully.');
    }

    public function destroy(RiskAssessment $riskAssessment): RedirectResponse
    {
        $this->authorize('merch_risk_assessment.delete');

        $riskAssessment->delete();

        return back()->with('success', 'Risk assessment deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'stylesOptions' => Style::query()->active()->orderBy('style_no')->get(),
            'seasonsOptions' => Season::query()->active()->orderBy('name')->get(),
            'factoriesOptions' => Factory::query()->active()->orderBy('name')->get(),
        ];
    }
}
