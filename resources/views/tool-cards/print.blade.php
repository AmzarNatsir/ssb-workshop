<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Card - {{ $toolCard->employee->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .card-container {
            width: 350px; /* ID card size approx */
            height: 550px;
            border: 2px solid #000;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .title {
            font-size: 18px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .photo-box {
            width: 120px;
            height: 150px;
            background: #eee;
            border: 1px solid #ccc;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .employee-info h2 {
            margin: 5px 0;
            font-size: 22px;
        }
        .employee-info p {
            margin: 0;
            color: #555;
            font-size: 14px;
        }
        .level-badge {
            margin: 20px 0;
            padding: 10px;
            color: #fff;
            border-radius: 5px;
            font-weight: bold;
            font-size: 24px;
        }
        .level-1 { background-color: #6c757d; }
        .level-2 { background-color: #0d6efd; }
        .level-3 { background-color: #dc3545; }

        .barcode-container {
            margin-top: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #999;
        }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="card-container">
        <div class="header">
            <div class="logo">SSB WORKSHOP</div>
            <div class="title">Tool Inventory Access</div>
        </div>

        <div class="photo-box">
            <!-- In real app, verify storage path -->
             <span>Pass Photo</span>
        </div>

        <div class="employee-info">
            <h2>{{ $toolCard->employee->name }}</h2>
            <p>ID: {{ $toolCard->employee->nik }}</p>
            <p>{{ $toolCard->employee->position ?? 'Staff' }}</p>
        </div>

        <div class="level-badge level-{{ $toolCard->access_level }}">
            LEVEL {{ $toolCard->access_level }}
        </div>

        <div class="barcode-container">
            @if($toolCard->code_type == 'BARCODE')
                <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                     <img src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($toolCard->uid, 'C128', 2, 60) }}" alt="barcode" />
                </div>
            @else
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($toolCard->uid) !!}
            @endif
             <p style="margin-top: 5px; font-family: monospace;">{{ $toolCard->uid }}</p>
        </div>

        <div class="footer">
            Issued: {{ $toolCard->updated_at->format('d M Y') }} <br>
            Authorized Signature
        </div>
    </div>

</body>
</html>
