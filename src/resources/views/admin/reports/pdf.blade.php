<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 3px 5px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <p>Generated: {{ now()->format('Y-m-d H:i') }}</p>
    <table>
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $val)
                        <td>{{ $val }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ max(1, count($headers)) }}">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
