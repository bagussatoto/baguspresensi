<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembatalan (VOID) — {{ $voidData['invoice_number'] ?? 'Kasir POS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Roboto:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Courier Prime', 'Courier New', Courier, monospace;
            color: #000000;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px;
            min-height: 100vh;
        }

        .no-print-bar {
            background: #1e293b;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-action {
            background: #ef4444;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            border-radius: 5px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action:hover {
            background: #dc2626;
        }

        .btn-toggle {
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
        }
        .btn-toggle.active {
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
        }

        /* THERMAL SLIP CONTAINER */
        .thermal-slip {
            background: #ffffff;
            border: 1px dashed #94a3b8;
            padding: 12px 10px;
            text-align: left;
            line-height: 1.35;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: width 0.2s ease;
        }

        .thermal-slip.paper-58mm {
            width: 58mm;
            max-width: 58mm;
            font-size: 10px;
        }

        .thermal-slip.paper-80mm {
            width: 80mm;
            max-width: 80mm;
            font-size: 12px;
            padding: 16px 14px;
        }

        .thermal-center { text-align: center; }
        .thermal-divider { border-bottom: 1px dashed #000000; margin: 6px 0; }
        .thermal-row { display: flex; justify-content: space-between; margin: 2px 0; align-items: flex-start; }
        .thermal-item-detail { font-size: 9px; color: #444; margin-top: 1px; }
        .paper-80mm .thermal-item-detail { font-size: 10.5px; }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .thermal-slip {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .thermal-slip.paper-58mm {
                width: 48mm !important;
                max-width: 48mm !important;
                font-size: 10px !important;
            }
            .thermal-slip.paper-80mm {
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
<body>

    {{-- TOP ACTION BAR --}}
    <div class="no-print-bar">
        <span>🧾 <strong>Cetak Slip VOID</strong> ({{ $voidData['invoice_number'] }})</span>
        <div style="display: flex; gap: 4px; margin: 0 8px;">
            <button type="button" class="btn-toggle {{ ($paperSize ?? '58mm') === '58mm' ? 'active' : '' }}" onclick="setPaper('58mm')">58mm</button>
            <button type="button" class="btn-toggle {{ ($paperSize ?? '58mm') === '80mm' ? 'active' : '' }}" onclick="setPaper('80mm')">80mm</button>
        </div>
        <button type="button" class="btn-action" onclick="window.print()">
            <span>🖨️</span> Cetak Sekarang
        </button>
        <button type="button" class="btn-toggle" onclick="window.close()" style="margin-left: 4px;">
            Tutup
        </button>
    </div>

    {{-- THERMAL VOID SLIP CONTENT --}}
    <div id="thermal-slip" class="thermal-slip {{ ($paperSize ?? '58mm') === '80mm' ? 'paper-80mm' : 'paper-58mm' }}">
        <div class="thermal-center" style="font-weight: bold; font-size: 13px; letter-spacing: 0.5px;">MAYA GRAHA KENCANA</div>
        <div class="thermal-center" style="font-size: 10px; font-weight: bold;">Cabang {{ $voidData['branch_name'] ?? 'Pusat' }}</div>
        <div class="thermal-center" style="font-size: 8.5px; color: #555;">{{ $voidData['branch_address'] ?? 'MGK Internet Cafe Network' }}</div>
        
        <div class="thermal-divider"></div>

        <div class="thermal-center" style="margin: 4px 0;">
            <div style="font-weight: 800; font-size: 11px; letter-spacing: 0.8px; border: 1.5px solid #000; padding: 2px 6px; display: inline-block;">
                *** STRUK PEMBATALAN (VOID) ***
            </div>
            <div style="font-size: 8.5px; font-weight: bold; margin-top: 2px;">
                BUKTI SAH RETUR FISIK KASIR
            </div>
        </div>

        <div class="thermal-divider"></div>

        <div class="thermal-row">
            <span>No. Invoice:</span>
            <span style="font-weight: bold; font-family: monospace; text-decoration: line-through;">{{ $voidData['invoice_number'] }}</span>
        </div>
        <div class="thermal-row">
            <span>Waktu Order:</span>
            <span>{{ $voidData['created_at'] }}</span>
        </div>
        <div class="thermal-row">
            <span>Waktu Batal:</span>
            <span style="font-weight: bold;">{{ $voidData['void_at'] }}</span>
        </div>
        <div class="thermal-row">
            <span>Kasir:</span>
            <span>{{ $voidData['cashier_name'] }}</span>
        </div>
        <div class="thermal-row">
            <span>Otorisator:</span>
            <span style="font-weight: bold;">{{ $voidData['supervisor_name'] }}</span>
        </div>
        <div class="thermal-row">
            <span>Pelanggan:</span>
            <span>{{ $voidData['customer_name'] }}</span>
        </div>
        <div class="thermal-row">
            <span>Alasan:</span>
            <span style="font-style: italic; max-width: 60%; text-align: right;">{{ $voidData['reason'] }}</span>
        </div>

        <div class="thermal-divider"></div>
        <div style="font-size: 9px; font-weight: bold; margin-bottom: 2px;">RINCIAN ITEM BATAL:</div>
        
        @foreach($voidData['items'] ?? [] as $item)
            <div style="margin-bottom: 3px;">
                <div class="thermal-row">
                    <span style="font-weight: 600; text-decoration: line-through;">{{ $item['item_name'] }}</span>
                    <span style="font-weight: bold;">-Rp {{ number_format($item['subtotal'] ?? ($item['qty'] * $item['unit_price']), 0, ',', '.') }}</span>
                </div>
                <div class="thermal-item-detail" style="color: #666;">
                    [BATAL] {{ $item['qty'] }} pcs x Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                </div>
            </div>
        @endforeach

        <div class="thermal-divider"></div>

        <div class="thermal-row" style="font-weight: bold; font-size: 12px; margin-top: 3px;">
            <span>TOTAL BATAL:</span>
            <span style="font-family: monospace;">-Rp {{ number_format($voidData['net_amount'] ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="thermal-row">
            <span>Metode Asal:</span>
            <span>{{ $voidData['payment_method'] ?? 'CASH' }}</span>
        </div>
        <div class="thermal-row">
            <span>Pengembalian:</span>
            <span style="font-weight: bold;">BATAL / REFUND KAS</span>
        </div>

        <div class="thermal-divider"></div>

        <div class="thermal-center" style="font-size: 8.5px; line-height: 1.35; margin: 4px 0;">
            Struk ini adalah bukti fisik sah retur stok inventaris.<br>
            <em>Wajib dilampirkan bersama uang laci saat Serah Terima Shift & Audit SoD.</em>
        </div>

        <div style="margin-top: 10px; display: flex; justify-content: space-between; text-align: center; font-size: 9px;">
            <div style="width: 46%;">
                <div>Kasir,</div>
                <div style="height: 28px;"></div>
                <div style="border-top: 1px dotted #000; padding-top: 2px; font-size: 8.5px;">({{ $voidData['cashier_name'] }})</div>
            </div>
            <div style="width: 46%;">
                <div>Supervisor/Admin,</div>
                <div style="height: 28px;"></div>
                <div style="border-top: 1px dotted #000; padding-top: 2px; font-size: 8.5px;">({{ $voidData['supervisor_name'] }})</div>
            </div>
        </div>

        <div class="thermal-divider" style="margin-top: 8px;"></div>
        <div class="thermal-center" style="font-size: 8px; color: #555;">
            *** MGK POS SECURITY AUDIT TRAIL ***
        </div>
    </div>

    <script>
        function setPaper(size) {
            const slip = document.getElementById('thermal-slip');
            const btns = document.querySelectorAll('.btn-toggle');
            if (size === '80mm') {
                slip.classList.remove('paper-58mm');
                slip.classList.add('paper-80mm');
            } else {
                slip.classList.remove('paper-80mm');
                slip.classList.add('paper-58mm');
            }
            btns.forEach(b => {
                b.classList.toggle('active', b.innerText === size);
            });
        }

        @if(request()->query('autoprint') === '1')
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 300);
            });
        @endif
    </script>
</body>
</html>
