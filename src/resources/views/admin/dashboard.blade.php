@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle('Merchandising Dashboard') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module" style="padding:20px 20px 30px;">
    @include('merchandising-trace::admin.partials.ui-kit')
    @include('merchandising-trace::admin.partials.dashboard-widget')
</div>
@endsection
