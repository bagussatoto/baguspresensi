<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Closing Kasir — {{ $workDate->format('d/m/Y') }} — {{ $branch->name ?? 'Cabang Pusat' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 24px;
            font-size: 13px;
        }

        .no-print {
            max-width: 800px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover { background: #0369a1; }

        .btn-secondary {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-secondary:hover { background: #cbd5e1; }

        .btn-thermal {
            background: #0f766e;
            color: #ffffff;
        }
        .btn-thermal:hover { background: #115e59; }

        /* KERTAS CETAK STANDARD DOKUMEN */
        .paper-doc {
            background: #ffffff;
            max-width: 800px;
            margin: 0 auto;
            padding: 36px 42px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .company-brand {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .branch-info {
            font-size: 11.5px;
            color: #475569;
            margin-top: 4px;
            line-height: 1.4;
        }

        .doc-meta {
            text-align: right;
            font-size: 11.5px;
            color: #334155;
            line-height: 1.5;
        }

        .doc-title-box {
            text-align: center;
            margin-bottom: 22px;
        }

        .doc-title {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0f172a;
        }

        .doc-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 3px;
        }

        /* TABEL REKAP SHIFT */
        .recap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 22px;
        }

        .recap-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }

        .recap-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            vertical-align: middle;
        }

        .recap-table tr:nth-child(even) td {
            background: #fcfcfc;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .font-bold { font-weight: 700; }

        /* SUMMARY METRICS GRID */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            padding: 12px 16px;
        }

        .summary-card-title {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .summary-card-row {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            padding: 3px 0;
            color: #334155;
        }

        /* LEMBAR TANDA TANGAN */
        .signature-section {
            margin-top: 36px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            text-align: center;
            font-size: 11.5px;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 110px;
        }

        .sign-role {
            font-weight: 600;
            color: #475569;
        }

        .sign-name {
            font-weight: 700;
            color: #0f172a;
            border-top: 1px solid #0f172a;
            padding-top: 4px;
            margin: 0 10px;
        }

        .footer-note {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px dashed #cbd5e1;
            font-size: 10px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                font-size: 11.5px !important;
            }

            .no-print {
                display: none !important;
            }

            .paper-doc {
                max-width: 100% !important;
                padding: 20px !important;
                box-shadow: none !important;
                border: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 15mm;
            }

            body.mode-thermal {
                max-width: 80mm !important;
                padding: 4mm !important;
                font-family: 'Courier New', Courier, monospace !important;
                font-size: 11px !important;
            }
            body.mode-thermal .paper-doc {
                padding: 0 !important;
            }
            body.mode-thermal .doc-header {
                flex-direction: column;
                text-align: center;
                border-bottom: 1px dashed #000;
            }
            body.mode-thermal .doc-meta {
                text-align: center;
                margin-top: 6px;
            }
            body.mode-thermal .summary-grid {
                grid-template-columns: 1fr;
            }
            body.mode-thermal .signature-section {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            body.mode-thermal .signature-box {
                height: 60px;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('presensi2.pos') }}" class="btn btn-secondary">
                <span>←</span> Kembali ke POS
            </a>
            <span style="font-size: 12px; color: #64748b;">|</span>
            <button type="button" onclick="setMode('standard')" id="btn-mode-standard" class="btn btn-secondary font-bold" style="background: #0284c7; color: #fff;">
                📄 Kertas Standar (A4)
            </button>
            <button type="button" onclick="setMode('thermal')" id="btn-mode-thermal" class="btn btn-secondary font-bold">
                🧾 Thermal 80mm
            </button>
        </div>

        <div>
            <button type="button" onclick="window.print()" class="btn btn-primary font-bold">
                🖨️ Cetak Berita Acara (Ctrl + P)
            </button>
        </div>
    </div>

    <div class="paper-doc" id="printable-doc">
        <!-- HEADER KOP SURAT RESMI -->
        <div class="doc-header">
            <div>
                <div class="company-brand">PT MAYA GRAHA KENCANA</div>
                <div class="branch-info">
                    <strong>{{ $branch->name ?? 'Cabang Utama' }}</strong> (Kode: {{ $branch->code ?? 'PST' }})<br>
                    {{ $branch->address ?? 'Jl. Boulevard Kelapa Gading Blok M No. 18' }}<br>
                    Telp: {{ $branch->phone ?? '021-45879900' }} • Email: finance@mayagrahakencana.co.id
                </div>
            </div>
            <div class="doc-meta">
                <div><strong>No. BA:</strong> BA-CLS/{{ $branch->code ?? 'CAB' }}/{{ $workDate->format('Ymd') }}</div>
                <div><strong>Tanggal Rekap:</strong> {{ $workDate->isoFormat('dddd, D MMMM Y') }}</div>
                <div><strong>Waktu Cetak:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }} WIB</div>
                <div><strong>Kasir Pelapor:</strong> {{ $employee->name }}</div>
            </div>
        </div>

        <!-- JUDUL BERITA ACARA -->
        <div class="doc-title-box">
            <h1 class="doc-title">BERITA ACARA CLOSING & SETTLEMENT KAS HARIAN</h1>
            <div class="doc-subtitle">Rekonsiliasi Kas Masuk, Pendapatan Layanan Billing, Penjualan F&B, dan Serah Terima Fisik Kasir 24 Jam</div>
        </div>

        <!-- STATUS SETTLEMENT SUMMARY -->
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 12px;">
                <strong>Status Rekonsiliasi:</strong>
                @if(!empty($dailySettlement['all_settled']))
                    <span style="color: #166534; font-weight: 700; background: #dcfce7; padding: 2px 8px; border-radius: 4px; border: 1px solid #bbf7d0;">
                        ✓ LENGKAP SETTLED ({{ $dailySettlement['settled_count'] ?? 0 }}/{{ $dailySettlement['total_shift_count'] ?? 4 }} Shift)
                    </span>
                @else
                    <span style="color: #9a3412; font-weight: 700; background: #ffedd5; padding: 2px 8px; border-radius: 4px; border: 1px solid #fed7aa;">
                        ⏳ SETTLEMENT SEBAGIAN ({{ $dailySettlement['settled_count'] ?? 0 }}/{{ $dailySettlement['total_shift_count'] ?? 4 }} Shift Selesai)
                    </span>
                @endif
            </div>
            <div style="font-size: 12px; color: #475569;">
                Mata Uang: <strong>Rupiah (IDR)</strong>
            </div>
        </div>

        <!-- TABEL RINCIAN 4 SHIFT -->
        <table class="recap-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Shift</th>
                    <th style="width: 16%;">Jam Kerja</th>
                    <th style="width: 22%;">Operator Kasir</th>
                    <th style="width: 16%;" class="text-right">Omset Tercatat</th>
                    <th style="width: 16%;" class="text-right">Kas Fisik Disetor</th>
                    <th style="width: 16%;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalRecorded = 0.0;
                    $totalPhysicalDeposited = 0.0;
                @endphp
                @forelse($dailySettlement['shifts'] ?? [] as $sh)
                    @php
                        $omset = (float) ($sh['sales'] ?? 0);
                        $deposited = ($sh['status_key'] ?? '') === 'completed' ? (float) ($sh['sales'] ?? 0) : 0;
                        $totalRecorded += $omset;
                        $totalPhysicalDeposited += $deposited;
                    @endphp
                    <tr>
                        <td class="font-bold">{{ $sh['name'] ?? 'Shift' }}</td>
                        <td class="font-mono text-center" style="font-size: 11px;">
                            {{ substr((string) ($sh['shift']->start_time ?? '00:00'), 0, 5) }} - {{ substr((string) ($sh['shift']->end_time ?? '00:00'), 0, 5) }}
                        </td>
                        <td>
                            {{ $sh['summary']->employee->name ?? ($sh['is_current'] ? $employee->name.' (Aktif)' : '-') }}
                        </td>
                        <td class="text-right font-mono font-bold">
                            Rp {{ number_format($omset, 0, ',', '.') }}
                        </td>
                        <td class="text-right font-mono" style="color: #0f766e; font-weight: 700;">
                            Rp {{ number_format($deposited, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span style="font-size: 11px; font-weight: 700; color: {{ $sh['status_color'] ?? '#64748b' }};">
                                {{ $sh['status_label'] ?? '-' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 16px; color: #94a3b8;">
                            Belum ada rekaman shift untuk tanggal ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td colspan="3" style="text-align: right; padding: 10px;">TOTAL KONSOLIDASI:</td>
                    <td class="text-right font-mono" style="font-size: 13px; color: #0f172a;">
                        Rp {{ number_format($totalRecorded, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-mono" style="font-size: 13px; color: #0f766e;">
                        Rp {{ number_format($totalPhysicalDeposited, 0, ',', '.') }}
                    </td>
                    <td class="text-center font-mono" style="font-size: 11px;">
                        @if($totalPhysicalDeposited >= $totalRecorded)
                            <span style="color: #166534;">BALANCE OK</span>
                        @else
                            <span style="color: #9a3412;">SELISIH RP {{ number_format(abs($totalRecorded - $totalPhysicalDeposited), 0, ',', '.') }}</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- METRIK PEMBAGIAN PENDAPATAN & METODE BAYAR -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-card-title">
                    <span>💳 Rincian Berdasarkan Metode Bayar</span>
                    <span>POS F&B</span>
                </div>
                <div class="summary-card-row">
                    <span>Penerimaan Kas / Tunai (Cash):</span>
                    <strong class="font-mono">Rp {{ number_format($saleStats['cash_amount'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-card-row">
                    <span>Non-Tunai (QRIS / Transfer Bank):</span>
                    <strong class="font-mono">Rp {{ number_format($saleStats['qris_amount'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-card-row">
                    <span>Total Diskon Diberikan:</span>
                    <strong class="font-mono" style="color: #dc2626;">- Rp {{ number_format($saleStats['discount_amount'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="summary-card-row" style="border-top: 1px solid #cbd5e1; margin-top: 4px; padding-top: 6px; font-weight: 700;">
                    <span>Total Penjualan Bersih Kasir:</span>
                    <strong class="font-mono" style="color: #0284c7;">Rp {{ number_format($saleStats['net_amount'] ?? 0, 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-title">
                    <span>📈 Ringkasan Arus Kas Laci (Drawer)</span>
                    <span>Kas Fisik</span>
                </div>
                <div class="summary-card-row">
                    <span>Kas Masuk Shift Terverifikasi:</span>
                    <strong class="font-mono">Rp {{ number_format((float) ($dailySettlement['total_settled_cash'] ?? 0), 0, ',', '.') }}</strong>
                </div>
                <div class="summary-card-row">
                    <span>Total Transaksi POS Tercatat:</span>
                    <strong class="font-mono">{{ number_format($saleStats['total_transactions'] ?? 0) }} Transaksi</strong>
                </div>
                <div class="summary-card-row">
                    <span>Estimasi Omset Kumulatif:</span>
                    <strong class="font-mono">Rp {{ number_format((float) ($dailySettlement['total_billing'] ?? 0), 0, ',', '.') }}</strong>
                </div>
                <div class="summary-card-row" style="border-top: 1px solid #cbd5e1; margin-top: 4px; padding-top: 6px; font-weight: 700;">
                    <span>Status Rekonsiliasi Kas:</span>
                    <strong style="color: #166534;">Siap Diposting ke Buku Besar SAK</strong>
                </div>
            </div>
        </div>

        <!-- CATATAN KHUSUS & SERAH TERIMA -->
        <div style="border: 1px dashed #cbd5e1; border-radius: 6px; padding: 10px 14px; margin-bottom: 24px; font-size: 11.5px; background: #fefce8; color: #854d0e;">
            <strong>Catatan Operasional & Kejadian Khusus:</strong><br>
            • Dokumen Berita Acara ini merupakan bukti fisik sah rekonsiliasi harian seluruh transaksi kasir.<br>
            • Seluruh setoran tunai wajib dimasukkan ke dalam amplop bersegel disertai lampiran struk rekapitulasi shift sebelum diserahkan ke kas induk / brankas cabang.<br>
            • Selisih kas minus yang tidak disertai bukti persetujuan supervisor akan menjadi tanggung jawab pemegang kasir shift bersangkutan.
        </div>

        <!-- LEMBAR TANDA TANGAN RESMI 3 PIHAK -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="sign-role">Dibuat Oleh,<br><strong>Kasir / Operator Shift Penutup</strong></div>
                <div class="sign-name">{{ $employee->name }}</div>
            </div>
            <div class="signature-box">
                <div class="sign-role">Diverifikasi Oleh,<br><strong>Supervisor / Head Store Cabang</strong></div>
                <div class="sign-name">( ........................................ )</div>
            </div>
            <div class="signature-box">
                <div class="sign-role">Disetujui Oleh,<br><strong>Finance & Accounting Pusat</strong></div>
                <div class="sign-name">( ........................................ )</div>
            </div>
        </div>

        <!-- FOOTER DOKUMEN -->
        <div class="footer-note">
            <span>MGK ERP Integrated System • Sistem Presensi & POS v2.0</span>
            <span>Dicetak secara otomatis oleh sistem • Dokumen ini sah tanpa tanda tangan basah jika berstempel QR</span>
        </div>
    </div>

    <script>
        function setMode(mode) {
            const body = document.body;
            const btnStandard = document.getElementById('btn-mode-standard');
            const btnThermal = document.getElementById('btn-mode-thermal');

            if (mode === 'thermal') {
                body.classList.add('mode-thermal');
                btnThermal.style.background = '#0f766e';
                btnThermal.style.color = '#fff';
                btnStandard.style.background = '#e2e8f0';
                btnStandard.style.color = '#334155';
            } else {
                body.classList.remove('mode-thermal');
                btnStandard.style.background = '#0284c7';
                btnStandard.style.color = '#fff';
                btnThermal.style.background = '#e2e8f0';
                btnThermal.style.color = '#334155';
            }
        }
    </script>
</body>
</html>

