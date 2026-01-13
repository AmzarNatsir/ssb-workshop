<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Tool IDs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            max-width: 1200px;
            margin: auto;
        }
        .label-card {
            background: white;
            border: 2px solid #000;
            text-align: center;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 350px; /* Fixed height for consistency */
            box-sizing: border-box;
            break-inside: avoid;
        }
        .label-header {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            height: 40px; /* Fixed header height */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .label-image {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .label-image img {
            max-width: 100%;
            max-height: 150px;
            object-fit: contain;
        }
        .label-barcode {
            margin-bottom: 5px;
        }
        .label-barcode canvas {
            max-width: 100%;
            height: 50px;
        }
        .label-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #000;
            color: #fff;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
        }
        .label-footer .rack {
            background: white;
            color: black;
            padding: 2px 5px;
            border-radius: 2px;
        }
        .label-footer .id-code {
             /* font-size: 16px; */
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .container {
                display: block; /* Utilize block to flow naturally */
            }
            .label-card {
                 /* Use float or inline-block for grid simulation in print if grid not supported well, 
                    but modern browsers handle grid in print ok. 
                    Let's ensure breaks are handled. */
                width: 23%; /* 4 per row approx */
                float: left;
                margin: 1%;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            /* Reset floats at end */
            .container::after {
                content: "";
                clear: both;
                display: table;
            }
            @page {
                margin: 10mm;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body onload="generateBarcodes()">

    <div class="container">
        @foreach($tools as $tool)
        <div class="label-card">
            <div class="label-header">
                {{ $tool->name }}
            </div>
            <div class="label-image">
                @if($tool->image)
                    <img src="{{ Storage::url($tool->image) }}" alt="Tool Image">
                @else
                    <div style="width: 100%; height: 100px; background: #eee; display: flex; align-items: center; justify-content: center;">No Image</div>
                @endif
            </div>
            <div class="label-barcode">
                <canvas class="barcode" data-code="{{ $tool->code }}"></canvas>
            </div>
            <div class="label-code" style="font-weight:bold; font-size: 16px; margin-bottom: 5px;">
                *{{ $tool->code }}*
            </div>
            <div class="label-footer">
                <span>{{ $tool->id }}</span>
                <span class="rack">{{ $tool->racks ? $tool->racks->name : 'No Rack' }}</span>
                <span>{{ $tool->tool_type ? substr($tool->tool_type->name, 0, 3) : 'UNK' }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        function generateBarcodes() {
            const canvases = document.querySelectorAll('.barcode');
            canvases.forEach(canvas => {
                const code = canvas.getAttribute('data-code');
                try {
                    JsBarcode(canvas, code, {
                        format: "CODE128",
                        displayValue: false, // We display it manually
                        height: 40,
                        margin: 0
                    });
                } catch (e) {
                    console.error("Barcode generation failed for " + code, e);
                }
            });
            // Auto print logic can be added here if desired
            // window.print();
        }
    </script>
</body>
</html>
