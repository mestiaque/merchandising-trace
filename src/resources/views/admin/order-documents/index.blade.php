@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Documents — ' . $salesContract->contract_no) }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')

    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">Order Documents — {{ $salesContract->contract_no }}</h5></div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Document</th><th>Reference</th><th></th></tr></thead>
                <tbody>
                    @forelse($salesContract->pos as $po)
                        <tr>
                            <td>Purchase Order</td>
                            <td>{{ $po->po_no }} — {{ $po->style->style_no ?? '-' }}</td>
                            <td><a href="{{ route('merchandising-trace.sales-contracts.pos.pdf', [$salesContract, $po]) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-file-pdf"></i> Download</a></td>
                        </tr>
                    @empty
                    @endforelse
                    @forelse($costSheets as $cs)
                        <tr>
                            <td>Cost Sheet</td>
                            <td>{{ $cs->cost_sheet_no }} ({{ ucfirst($cs->status) }})</td>
                            <td><a href="{{ route('merchandising-trace.cost-sheets.pdf', $cs) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-file-pdf"></i> Download</a></td>
                        </tr>
                    @empty
                    @endforelse
                    @forelse($riskAssessments as $ra)
                        <tr>
                            <td>Risk Assessment</td>
                            <td>{{ $ra->style->style_no ?? '-' }} — {{ $ra->category ?? 'N/A' }}</td>
                            <td><a href="{{ route('merchandising-trace.risk-assessments.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i> View</a></td>
                        </tr>
                    @empty
                    @endforelse
                    @if($salesContract->pos->isEmpty() && $costSheets->isEmpty() && $riskAssessments->isEmpty())
                        <tr><td colspan="3" class="text-center text-muted">No order-wise documents yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $salesContract->contract_no }} — Document Checklist</h5>
            @if(!$salesContract->hasOutstandingMandatoryDocuments() && $salesContract->status !== 'closed')
                @can('merch_sales_contract.edit')
                    <form method="POST" action="{{ route('merchandising-trace.sales-contracts.close', $salesContract) }}" onsubmit="return confirm('Close this order?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Close Order</button>
                    </form>
                @endcan
            @elseif($salesContract->status !== 'closed')
                <span class="badge bg-danger">Mandatory documents missing — cannot close</span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead><tr><th>Document</th><th>Mandatory</th><th>Due Date</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($salesContract->documents as $doc)
                        <tr class="{{ $doc->isOverdue() ? 'table-danger' : '' }}">
                            <td>{{ $doc->name }}</td>
                            <td>{{ $doc->is_mandatory ? 'Yes' : 'No' }}</td>
                            <td>{{ optional($doc->due_date)->format('Y-m-d') ?? '-' }} @if($doc->isOverdue())<span class="badge bg-danger">Overdue</span>@endif</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($doc->status) }}</span></td>
                            <td>
                                @can('merch_documentation.edit')
                                    <form method="POST" action="{{ route('merchandising-trace.order-documents.upload', [$salesContract, $doc]) }}" enctype="multipart/form-data" class="d-inline-flex gap-1">
                                        @csrf
                                        <input type="file" name="file" class="form-control form-control-sm" required>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Upload</button>
                                    </form>
                                    @if($doc->status === 'uploaded')
                                        <form method="POST" action="{{ route('merchandising-trace.order-documents.approve', [$salesContract, $doc]) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No checklist generated (no matching document template).</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
