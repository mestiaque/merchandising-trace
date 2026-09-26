@extends('printMaster2', ['hideGeneratedNote' => true])

@section('title', $sheet->post_cost_no . ' — Post Cost Sheet')

@push('css')
<style>
    @page { size: A4 landscape; margin: 8mm; }
</style>
@endpush

@section('contents')
    @include('merchandising-trace::admin.post-cost-sheets.partials.sheet', ['sheet' => $sheet])
@endsection
