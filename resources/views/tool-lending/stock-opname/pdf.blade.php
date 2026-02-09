<!DOCTYPE html>
<html>
<head>
    <title>Stock Opname Report - {{ $opname->opname_no }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .opname-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 10px; }
        .bg-success { background-color: #d4edda; color: #155724; }
        .bg-danger { background-color: #f8d7da; color: #721c24; }
        .bg-warning { background-color: #fff3cd; color: #856404; }
        .summary-box { border: 1px solid #ddd; padding: 10px; margin-bottom: 20px; }
        .footer-signatures { margin-top: 50px; }
        .signature-box { width: 30%; display: inline-block; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>STOCK OPNAME TOOLS REPORT</h2>
        <h3>{{ $opname->opname_no }}</h3>
    </div>

    <div class="opname-info">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;">
                    <strong>Period:</strong> {{ $opname->period }}<br>
                    <strong>Start Date:</strong> {{ \Carbon\Carbon::parse($opname->start_date)->format('d M Y') }}<br>
                    <strong>End Date:</strong> {{ $opname->end_date ? \Carbon\Carbon::parse($opname->end_date)->format('d M Y') : '-' }}<br>
                </td>
                <td style="border: none; width: 50%;">
                    <strong>PIC:</strong> {{ $opname->creator->name ?? 'Admin' }}<br>
                    <strong>Status:</strong> {{ $opname->status }}<br>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <strong>SUMMARY:</strong>
        <ul>
            <li>Total Tools Checked: {{ $stats['total'] }}</li>
            <li>Physical Missing: {{ $stats['missing'] }}</li>
            <li>Manual Findings (Excess): {{ $stats['excess'] }}</li>
            <li>Damaged Tools: {{ $stats['damaged'] }}</li>
        </ul>
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Tool Name</th>
                <th>Location</th>
                <th>System Status</th>
                <th>Physical Status</th>
                <th>Condition</th>
                <th>Mismatch</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opname->details as $detail)
            <tr>
                <td>{{ $detail->tool->code }}</td>
                <td>{{ $detail->tool->name }}</td>
                <td>{{ $detail->physical_location }}</td>
                <td>{{ $detail->system_status }}</td>
                <td>{{ $detail->physical_exist }}</td>
                <td>{{ $detail->physical_condition }}</td>
                <td>{{ $detail->mismatch ? 'YES' : 'NO' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-signatures">
        <div class="signature-box">
            <p>Prepared By,</p>
            <br><br><br>
            <p><strong>( {{ $opname->creator->name ?? 'Tool Admin' }} )</strong></p>
            <p>Tool Admin</p>
        </div>
        <div class="signature-box">
            <p>Verified By,</p>
            <br><br><br>
            <p><strong>( ________________ )</strong></p>
            <p>Tool Leader</p>
        </div>
        <div class="signature-box">
            <p>Approved By,</p>
            <br><br><br>
            <p><strong>( ________________ )</strong></p>
            <p>Workshop Manager</p>
        </div>
    </div>
</body>
</html>
