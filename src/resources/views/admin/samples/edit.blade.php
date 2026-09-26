@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Edit Sample') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit Sample — {{ $sample->sample_no }}</h4>
            <a href="{{ route('merchandising-trace.samples.show', $sample) }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('merchandising-trace.samples.update', $sample) }}">
                @csrf
                @method('PUT')
                @include('merchandising-trace::admin.samples.partials.form')
                <button type="submit" class="btn btn-primary mt-3 btn-sm">Update Sample</button>
                <a href="{{ route('merchandising-trace.samples.show', $sample) }}" class="btn btn-light mt-3 btn-sm">Cancel</a>
            </form>
        </div>
    </div>
</div>

@include('merchandising-trace::admin.partials.select2-init')
@endsection
