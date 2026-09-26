@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Add BOM') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Add BOM</h4>
            <a href="{{ route('merchandising-trace.boms.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.boms.store') }}" enctype="multipart/form-data">
                @csrf
                @include('merchandising-trace::admin.boms.partials.form')
                <button type="submit" class="btn btn-primary mt-3 btn-sm">Save BOM</button>
                <a href="{{ route('merchandising-trace.boms.index') }}" class="btn btn-light mt-3 btn-sm">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
