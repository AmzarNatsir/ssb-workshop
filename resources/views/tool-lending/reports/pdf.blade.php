<!DOCTYPE html>
<html>
<head>
    <title>Tool Transactions Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 8pt; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Tools Transaction Report</h2>
        <p>Generated on: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>LN Number</th>
                <th>Tool</th>
                <th>Borrower</th>
                <th>Out Date</th>
                <th>In Date</th>
                <th>Status</th>
                <th>Condition</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $tx)
                <tr>
                    <td>{{ $tx->loan_number }}</td>
                    <td>{{ $tx->tool->name }} ({{ $tx->tool->code }})</td>
                    <td>{{ $tx->employee->name }}</td>
                    <td>{{ $tx->loan_date->format('d/m/y H:i') }}</td>
                    <td>{{ $tx->actual_return_date ? $tx->actual_return_date->format('d/m/y H:i') : '-' }}</td>
                    <td>{{ $tx->status }}</td>
                    <td>{{ $tx->return_condition ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Page <span class="page"></span>
    </div>
</body>
</html>
