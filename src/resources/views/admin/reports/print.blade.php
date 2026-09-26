@extends('printMaster2')

@section('title', $title)

@section('contents')
    <div class="print-header">
        <div class="company-info">
            @if(general() && general()->logo())
                <img src="{{ asset(general()->logo()) }}" alt="Logo" class="company-logo">
            @endif
            <div class="company-name">{{ general()->title ?? config('merchandising-trace.company.name') }}</div>
            <div style="text-align: end; width: 42mm;">
                <div class="company-address">{{ general()->address_one ?? '' }}</div>
            </div>
        </div>
        <div style="font-weight: bold; text-transform: uppercase;">
            {{ $title }}
            <span class="print-time"><i>{{ now()->format('d-m-Y H:i:s') }}</i></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">#</th>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    @foreach($row as $val)
                        <td class="{{ is_numeric($val) ? 'text-right' : '' }}">{{ is_float($val) ? number_format($val, 2) : $val }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) + 1 }}" class="text-center">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
