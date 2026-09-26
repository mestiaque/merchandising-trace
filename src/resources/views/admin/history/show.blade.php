@extends(adminTheme() . 'layouts.app')

@section('title')
    <title>{{ websiteTitle($title . ' — History') }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 merch-module">
    @include('merchandising-trace::admin.partials.alerts')
    @include('merchandising-trace::admin.partials.ui-kit')

    @php
        $moduleColors = ['Merchandising' => 'primary', 'Production' => 'success', 'Inventory' => 'warning'];
    @endphp

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ $title }}</h5>
                <div class="text-muted small">{{ $subtitle }}</div>
            </div>
            <div class="d-flex gap-2">
                @if($subjectType === 'po')
                    <a href="{{ route('merchandising-trace.history.style', $subject->style_id) }}" class="btn btn-light btn-sm">View full Style history</a>
                @endif
                <a href="{{ route('merchandising-trace.history.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Search</a>
            </div>
        </div>
        <div class="card-body py-2">
            <div class="d-flex gap-3 flex-wrap small">
                <span><span class="badge badge-primary">&nbsp;</span> Merchandising</span>
                <span><span class="badge badge-success">&nbsp;</span> Production</span>
                <span><span class="badge badge-warning">&nbsp;</span> Inventory</span>
                <span class="text-muted ml-auto">{{ $events->count() }} event(s)</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($events->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fa-solid fa-timeline fa-2x mb-2 d-block"></i>
                    No dated events found yet for this {{ $subjectType === 'po' ? 'PO' : 'style' }}.
                </div>
            @else
                <div class="history-timeline">
                    @foreach($events as $event)
                        <div class="history-event">
                            <div class="history-event-dot bg-{{ $moduleColors[$event['module']] ?? 'secondary' }}">
                                <i class="fa-solid {{ $event['icon'] }}"></i>
                            </div>
                            <div class="history-event-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge badge-{{ $moduleColors[$event['module']] ?? 'secondary' }} mr-1">{{ $event['module'] }}</span>
                                        <strong>{{ $event['title'] }}</strong>
                                        @if($event['detail'])
                                            <div class="text-muted small">{{ $event['detail'] }}</div>
                                        @endif
                                    </div>
                                    <span class="text-muted small text-nowrap ml-2">{{ $event['date']->format('Y-m-d') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .history-timeline {
        position: relative;
        padding-left: 2rem;
    }
    .history-timeline::before {
        content: '';
        position: absolute;
        left: .65rem;
        top: .25rem;
        bottom: .25rem;
        width: 2px;
        background: #e5e7eb;
    }
    .history-event {
        position: relative;
        display: flex;
        gap: .75rem;
        margin-bottom: 1rem;
    }
    .history-event-dot {
        position: absolute;
        left: -2rem;
        top: 0;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .65rem;
        flex-shrink: 0;
    }
    .history-event-body {
        flex: 1;
        background: #f8f9fa;
        border-radius: .375rem;
        padding: .6rem .85rem;
    }
</style>
@endsection
