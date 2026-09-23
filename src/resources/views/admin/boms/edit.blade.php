@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Edit BOM') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit BOM — {{ $bom->bom_no }}</h5>
            <a href="{{ route('merchandising-trace.boms.show', $bom) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.boms.update', $bom) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('merchandising-trace::admin.boms.partials.form')
                <button type="submit" class="btn btn-primary mt-3">Update BOM</button>
                <a href="{{ route('merchandising-trace.boms.show', $bom) }}" class="btn btn-light mt-3">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
