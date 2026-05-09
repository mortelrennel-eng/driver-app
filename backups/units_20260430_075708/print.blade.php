<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Units Management Report - {{ date('Y-m-d') }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 11pt;
            color: #1a202c;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-left h1 {
            margin: 0;
            color: #2d3748;
            font-size: 24pt;
            letter-spacing: -0.02em;
        }
        .header-left p {
            margin: 5px 0 0 0;
            color: #718096;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .header-right {
            text-align: right;
            color: #718096;
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: 700;
            text-align: left;
            padding: 12px 10px;
            border-bottom: 2px solid #edf2f7;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.05em;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #edf2f7;
            font-size: 10pt;
            vertical-align: top;
        }
        .unit-number {
            font-weight: 700;
            color: #2d3748;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-active { background-color: #def7ec; color: #03543f; }
        .badge-maintenance { background-color: #fef3c7; color: #92400e; }
        .badge-coding { background-color: #fde2e2; color: #9b1c1c; }
        
        .footer {
            margin-top: 50px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            text-align: center;
            font-size: 9pt;
            color: #a0aec0;
        }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .header { margin-top: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="background: #ebf8ff; padding: 10px; text-align: center; border-bottom: 1px solid #bee3f8; color: #2b6cb0; font-size: 12px;">
        This is a print-optimized view. The print dialog should open automatically. Use <strong>"Save as PDF"</strong> to download.
        <button onclick="window.print()" style="margin-left: 20px; padding: 5px 15px; background: #3182ce; color: white; border: none; border-radius: 4px; cursor: pointer;">Open Print Dialog</button>
    </div>

    <div class="header">
        <div class="header-left">
            <img src="{{ asset('uploads/logo.png') }}" alt="Euro System Logo" style="height: 50px; margin-bottom: 5px; display: block;">
            <p style="margin-top: 5px;">Units & Drivers Management Report</p>
        </div>
        <div class="header-right">
            <div>Report Generated:</div>
            <div style="font-weight: bold; color: #2d3748;">{{ date('F d, Y H:i:s') }}</div>
            <div style="margin-top: 5px;">Total Units: <strong>{{ count($units) }}</strong></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Unit Info</th>
                <th>Primary Driver (D1)</th>
                <th>Secondary Driver (D2)</th>
                <th style="text-align: center;">Drivers</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($units as $unit)
            <tr>
                <td>
                    <div class="unit-number">{{ $unit->plate_number }}</div>
                    <div style="font-size: 8pt; color: #a0aec0;">{{ $unit->make }} {{ $unit->model }} ({{ $unit->year }})</div>
                </td>
                <td>{{ $unit->driver1_name ?? '---' }}</td>
                <td>{{ $unit->driver2_name ?? '---' }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $unit->driver_count }}</td>
                <td style="font-weight: 600;">₱{{ number_format($unit->boundary_rate, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} Euro Performance Taxi System. All rights reserved.
    </div>
</body>
</html>
