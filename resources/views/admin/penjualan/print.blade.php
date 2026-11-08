<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan — {{ $sale->invoice_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000000;
            background: #ffffff;
            padding: 10px;
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
        }

        body.paper-58mm {
            max-width: 220px;
            font-size: 10px;
        }

        body.paper-80mm {
            max-width: 320px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .company-sub {
            font-size: 10px;
            margin-top: 2px;
        }

        .meta-table {
            width: 100%;
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 8px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .items-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
        }

        .items-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 2px 0;
        }

        .summary-row.bold {
            font-size: 13px;
            font-weight: bold;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            margin: 4px 0;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 8px;
            line-height: 1.4;
        }

        .no-print {
            text-align: center;
            margin-bottom: 12px;
        }

        .btn-print {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-family: sans-serif;
            font-size: 12px;
            cursor: pointer;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0 !important;
                margin: 0 !important;
            }
            body.paper-58mm {
                width: 48mm !important;
                max-width: 48mm !important;
                font-size: 10px !important;
            }
            body.paper-80mm {
                width: 72mm !important;
                max-width: 72mm !important;
                font-size: 12px !important;
            }
            @page {
                size: auto;
                margin: 0mm;
            }
        }
    </style>
</head>
<body class="paper-58mm" id="receipt-body">
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 8px;">
        <div style="display: flex; gap: 4px;">
            <button type="button" id="btn-58" onclick="setPaper('58mm')" style="background: #0284c7; color: #fff; border: 1px solid #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: bold;">58mm</button>
            <button type="button" id="btn-80" onclick="setPaper('80mm')" style="background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: bold;">80mm</button>
        </div>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk (Ctrl + P)</button>
    </div>

    <div class="header">
        <div class="company-name">MAYA GRAHA KENCANA</div>
        <div class="company-sub">Cabang {{ $sale->branch->name ?? $sale->employee->branch->name ?? 'Pusat' }}</div>
        <div class="company-sub">{{ $sale->branch->address ?? 'Jl. Boulevard Kelapa Gading Blok M No. 18' }}</div>
        <div class="company-sub">Telp: {{ $sale->branch->phone ?? '021-45879900' }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td>No. Trx</td>
            <td>: {{ $sale->invoice_number }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>: {{ $sale->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ $sale->employee->name ?? 'Kasir MGK' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>: {{ $sale->customer_name }}</td>
        </tr>
        @if ($sale->pcStation)
            <tr>
                <td>Unit PC</td>
                <td>: {{ $sale->pcStation->station_code }}</td>
            </tr>
        @endif
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Harga</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td style="text-align: center;">{{ $item->qty }}</td>
                    <td style="text-align: right;">{{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($sale->total_billing > 0)
        <div class="summary-row">
            <span>Billing PC:</span>
            <span>Rp {{ number_format((float) $sale->total_billing, 0, ',', '.') }}</span>
        </div>
    @endif
    @if ($sale->total_fnb > 0)
        <div class="summary-row">
            <span>F&B / Snack:</span>
            <span>Rp {{ number_format((float) $sale->total_fnb, 0, ',', '.') }}</span>
        </div>
    @endif

    <div class="summary-row bold">
        <span>TOTAL:</span>
        <span>Rp {{ number_format((float) $sale->net_amount, 0, ',', '.') }}</span>
    </div>

    <div class="summary-row">
        <span>Metode Bayar:</span>
        <span>{{ strtoupper($sale->payment_method) }}</span>
    </div>
    <div class="summary-row">
        <span>Dibayar:</span>
        <span>Rp {{ number_format((float) ($sale->amount_paid ?: $sale->net_amount), 0, ',', '.') }}</span>
    </div>
    <div class="summary-row">
        <span>Kembalian:</span>
        <span>Rp {{ number_format((float) $sale->change_amount, 0, ',', '.') }}</span>
    </div>

    @if ($sale->payments->isNotEmpty() && $sale->payments->first()->payment_number)
        <div class="summary-row" style="font-size: 10px; color: #555; margin-top: 4px;">
            <span>Ref Bayar:</span>
            <span>{{ $sale->payments->first()->payment_number }}</span>
        </div>
    @endif

    <div class="footer">
        Terima Kasih atas Kunjungan Anda!<br>
        MGK Cybercore & Internet Cafe<br>
        <em>Simpan struk ini sebagai bukti pembayaran sah.</em>
    </div>

    <script>
        function setPaper(size) {
            const body = document.getElementById('receipt-body');
            const btn58 = document.getElementById('btn-58');
            const btn80 = document.getElementById('btn-80');
            if (size === '80mm') {
                body.className = 'paper-80mm';
                btn80.style.background = '#0284c7';
                btn80.style.color = '#fff';
                btn58.style.background = '#e2e8f0';
                btn58.style.color = '#334155';
            } else {
                body.className = 'paper-58mm';
                btn58.style.background = '#0284c7';
                btn58.style.color = '#fff';
                btn80.style.background = '#e2e8f0';
                btn80.style.color = '#334155';
            }
        }
        window.addEventListener('load', function() {
            // Uncomment jika ingin auto-print saat halaman dibuka
            // window.print();
        });
    </script>
</body>
</html>
