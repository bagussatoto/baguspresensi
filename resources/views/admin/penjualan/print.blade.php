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
            font-size: 12px;
            color: #000000;
            background: #ffffff;
            padding: 15px;
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
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
                display: none;
            }
            body {
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk (Ctrl + P)</button>
    </div>

    <div class="header">
        <div class="company-name">MAYA GRAHA KENCANA</div>
        <div class="company-sub">Jl. Boulevard Kelapa Gading Blok M No. 18</div>
        <div class="company-sub">Telp: 021-45879900 • Jakarta Utara</div>
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
        window.addEventListener('load', function() {
            // Uncomment jika ingin auto-print saat halaman dibuka
            // window.print();
        });
    </script>
</body>
</html>
