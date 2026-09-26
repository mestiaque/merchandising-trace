@extends('printMaster2', ['hideGeneratedNote' => true])

@section('title', $costSheet->cost_sheet_no . ' — Open Cost Sheet')

@push('css')
<style>
    @page { size: A4 portrait; margin: 8mm; }
    .container { max-width: 1000px; }
</style>
@endpush

@section('contents')
    @include('merchandising-trace::admin.cost-sheets.partials.sheet', ['forPdf' => false])
@endsection
