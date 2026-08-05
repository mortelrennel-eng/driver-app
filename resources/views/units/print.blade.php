<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Units Management Report &mdash; {{ date('Y-m-d') }}</title>
    <style>
        @page { margin: 0; size: auto; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #fff; font-family: 'Segoe UI', system-ui, sans-serif; padding: 8mm 15mm 15mm 15mm; color: #111; font-size: 11px; }
        h1 { text-align: center; font-size: 20px; font-weight: 900; text-transform: uppercase; letter-spacing: .15em; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 10px; color: #64748b; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; margin-bottom: 32px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { border-bottom: 1px solid #000; }
        thead th { padding: 8px 10px; font-size: 9px; text-transform: uppercase; font-weight: 800; text-align: left; letter-spacing: .05em; }
        thead th.center { text-align: center; }
        thead th.right { text-align: right; }
        tr { page-break-inside: avoid; break-inside: avoid; }
        tbody tr { border-bottom: 1px solid #eee; }
        td { padding: 8px 10px; font-size: 11px; vertical-align: top; }
        td.center { text-align: center; font-weight: bold; }
        td.right { text-align: right; font-weight: 900; }
        .unit-number { font-weight: 900; font-size: 12px; color: #000; }
        .unit-desc { font-size: 9px; color: #64748b; text-transform: uppercase; margin-top: 2px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 16px; border-top: 1px dashed #ccc; font-size: 9px; color: #777; }
        img { max-height: 60px !important; width: auto !important; display: block; margin: 0 auto 15px auto; }
        .header-meta { display: flex; justify-content: space-between; font-size: 10px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 10px; color: #333; }
        .print-only { display: block !important; }
    </style>
</head>
<body onload="window.print()">
    <img src="{{ asset('image/logo.png') }}" alt="Euro System Logo">
    <h1>UNITS & DRIVERS MANAGEMENT REPORT</h1>
    <p class="subtitle">EURO TAXI MANAGEMENT SYSTEM &mdash; OFFICIAL RECORD</p>
    
    <div class="header-meta">
        <div>Total Registered Units: {{ count($units) }}</div>
        <div>Timestamp: {{ date('M d, Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Unit Info</th>
                <th>Primary Driver (D1)</th>
                <th>Secondary Driver (D2)</th>
                <th class="center">Drivers</th>
                <th class="right">Boundary Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($units as $unit)
            <tr>
                <td>
                    <div class="unit-number">{{ $unit->plate_number }}</div>
                    <div class="unit-desc">{{ $unit->make }} {{ $unit->model }} ({{ $unit->year }})</div>
                </td>
                <td>{{ $unit->driver1_name ?? '---' }}</td>
                <td>{{ $unit->driver2_name ?? '---' }}</td>
                <td class="center">{{ $unit->driver_count }}</td>
                <td class="right">₱{{ number_format($unit->boundary_rate, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Authenticated Units & Drivers Record &mdash; Generated: {{ date('m/d/Y, h:i:s A') }}</p>
    </div>
</body>
</html>
