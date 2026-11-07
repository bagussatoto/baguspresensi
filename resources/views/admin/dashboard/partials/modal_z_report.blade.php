{{-- MODAL Z-REPORT HARIAN (SETTLEMENT 24 JAM) --}}
<div id="modal-daily-z-report" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); z-index: 9999; align-items: center; justify-content: center; padding: 16px; backdrop-filter: blur(4px);">
    <div class="modal-card" style="background: #0f172a; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 12px; width: 100%; max-width: 950px; max-height: 92vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        
        {{-- MODAL HEADER (TIDAK DICETAK) --}}
        <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: rgba(16, 185, 129, 0.12); border-bottom: 1px solid rgba(16, 185, 129, 0.25);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">🖨️</span>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #34d399;">
                        Z-Report Harian & Rekonsiliasi 4 Shift (24 Jam)
                    </h3>
                    <p style="margin: 2px 0 0 0; font-size: 11.5px; color: #94a3b8;">
                        Settlement harian resmi penutup buku: Omset, Setoran Kasir, Modal Laci, Audit Stok, & Hardware
                    </p>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" onclick="printDailyZReport()" style="background: #059669; hover: #10b981; color: #ffffff; border: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <span>⎙</span> Cetak Dokumen / PDF
                </button>
                <button type="button" onclick="closeDailyZReportModal()" style="background: rgba(255, 255, 255, 0.1); color: #cbd5e1; border: none; padding: 6px 12px; border-radius: 6px; font-size: 13px; cursor: pointer;">
                    ✕ Tutup
                </button>
            </div>
        </div>

        {{-- AREA DOKUMEN CETAK (PRINTABLE AREA) --}}
        <div id="z-report-printable-area" style="padding: 24px; overflow-y: auto; color: #e2e8f0; font-family: system-ui, -apple-system, sans-serif;">
            
            {{-- KOP SURAT / HEADER RESMI MGK --}}
            <div style="text-align: center; border-bottom: 2px dashed #475569; padding-bottom: 14px; margin-bottom: 16px;">
                <h2 style="margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 1px; color: #f8fafc;">
                    MAYA GRAHA KENCANA (MGK)
                </h2>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 3px;">
                    Pusat Internet & Game Station • Layanan Print & Fotocopy • Cafe & Resto
                </div>
                <div style="margin-top: 8px; display: inline-block; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); padding: 4px 14px; border-radius: 20px;">
                    <strong style="color: #34d399; font-size: 13px; letter-spacing: 0.5px;">
                        BERITA ACARA SETTLEMENT HARIAN (Z-REPORT 24 JAM)
                    </strong>
                </div>
                <div style="margin-top: 8px; font-size: 11.5px; color: #cbd5e1; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                    <span>📅 Hari / Tanggal: <strong style="color: #f1f5f9;">{{ $zReportData['date_formatted'] ?? date('d M Y') }}</strong></span>
                    <span>🏢 Cabang: <strong style="color: #f1f5f9;">{{ $zReportData['branch_name'] ?? 'MGK Pusat' }}</strong></span>
                    <span>⏱️ Waktu Cetak: <strong style="color: #f1f5f9;">{{ $zReportData['printed_at'] ?? date('H:i') }}</strong></span>
                </div>
            </div>

            {{-- KPI RINGKASAN GRAND TOTAL 24 JAM --}}
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px;">
                <div style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 8px; padding: 12px; text-align: center;">
                    <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Total Omset Kotor (24 Jam)</div>
                    <div style="font-size: 17px; font-weight: 800; color: #38bdf8; margin-top: 4px;">
                        Rp {{ number_format($zReportData['grand_total_revenue'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">Billing + F&B + Jasa Print</div>
                </div>

                <div style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; padding: 12px; text-align: center;">
                    <div style="font-size: 11px; color: #a7f3d0; text-transform: uppercase;">Total Kas Disetor / Tarik</div>
                    <div style="font-size: 17px; font-weight: 800; color: #34d399; margin-top: 4px;">
                        Rp {{ number_format($zReportData['grand_total_deposited'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div style="font-size: 9.5px; color: #6ee7b7; margin-top: 2px;">Disetor Brankas / Bank</div>
                </div>

                <div style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; padding: 12px; text-align: center;">
                    <div style="font-size: 11px; color: #fde68a; text-transform: uppercase;">Modal Laci Ditinggal</div>
                    <div style="font-size: 17px; font-weight: 800; color: #fbbf24; margin-top: 4px;">
                        Rp {{ number_format($zReportData['final_retained_cash'] ?? 150000, 0, ',', '.') }}
                    </div>
                    <div style="font-size: 9.5px; color: #fef08a; margin-top: 2px;">Modal Kasir Shift 1 Besok</div>
                </div>

                <div style="background: rgba(30, 41, 59, 0.8); border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 8px; padding: 12px; text-align: center;">
                    <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase;">Net Selisih Kasir</div>
                    @php $netCashDiff = (float) ($zReportData['grand_total_cash_difference'] ?? 0); @endphp
                    <div style="font-size: 17px; font-weight: 800; color: {{ $netCashDiff == 0 ? '#34d399' : ($netCashDiff < 0 ? '#f87171' : '#38bdf8') }}; margin-top: 4px;">
                        {{ $netCashDiff == 0 ? '✓ Pas (Rp 0)' : ($netCashDiff < 0 ? '-Rp ' . number_format(abs($netCashDiff), 0, ',', '.') : '+Rp ' . number_format($netCashDiff, 0, ',', '.')) }}
                    </div>
                    <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">Akumulasi Selisih Laci</div>
                </div>
            </div>

            {{-- TABEL BREAKDOWN 4 SHIFT --}}
            <div style="border: 1px solid #334155; border-radius: 8px; overflow: hidden; margin-bottom: 16px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; text-align: left;">
                    <thead>
                        <tr style="background: #1e293b; color: #f1f5f9; border-bottom: 2px solid #475569;">
                            <th style="padding: 10px 8px;">Shift & Jam</th>
                            <th style="padding: 10px 8px;">Kasir</th>
                            <th style="padding: 10px 8px; text-align: right;">Billing</th>
                            <th style="padding: 10px 8px; text-align: right;">F&B</th>
                            <th style="padding: 10px 8px; text-align: right;">Print</th>
                            <th style="padding: 10px 8px; text-align: right;">Total Omset</th>
                            <th style="padding: 10px 8px; text-align: right;">Disetor</th>
                            <th style="padding: 10px 8px; text-align: right;">Modal Ditinggal</th>
                            <th style="padding: 10px 8px; text-align: right;">Selisih Kas</th>
                            <th style="padding: 10px 8px; text-align: center;">Stok & HW</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zReportData['shifts'] ?? [] as $s)
                        <tr style="border-bottom: 1px solid #334155; {{ $loop->even ? 'background: rgba(30, 41, 59, 0.4);' : '' }}">
                            <td style="padding: 8px;">
                                <strong style="color: #f8fafc;">{{ $s['shift_name'] }}</strong><br>
                                <span style="font-size: 10px; color: #94a3b8;">{{ $s['shift_hours'] }}</span>
                            </td>
                            <td style="padding: 8px; font-weight: 600; color: #cbd5e1;">
                                {{ $s['cashier_name'] }}
                            </td>
                            <td style="padding: 8px; text-align: right; font-family: monospace;">
                                {{ number_format($s['billing_amount'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 8px; text-align: right; font-family: monospace;">
                                {{ number_format($s['fnb_amount'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 8px; text-align: right; font-family: monospace;">
                                {{ number_format($s['print_amount'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 8px; text-align: right; font-family: monospace; font-weight: 700; color: #38bdf8;">
                                {{ number_format($s['total_revenue'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 8px; text-align: right; font-family: monospace; color: #34d399;">
                                {{ number_format($s['deposited_amount'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 8px; text-align: right; font-family: monospace; color: #fbbf24;">
                                {{ number_format($s['retained_drawer_cash'], 0, ',', '.') }}
                            </td>
                            <td style="padding: 8px; text-align: right; font-family: monospace; font-weight: 600; color: {{ $s['cash_difference'] == 0 ? '#94a3b8' : ($s['cash_difference'] < 0 ? '#f87171' : '#38bdf8') }};">
                                {{ $s['cash_difference'] == 0 ? '0' : ($s['cash_difference'] < 0 ? '-' . number_format(abs($s['cash_difference']), 0, ',', '.') : '+' . number_format($s['cash_difference'], 0, ',', '.')) }}
                            </td>
                            <td style="padding: 8px; text-align: center;">
                                @if($s['discrepancy_count'] > 0)
                                    <span style="display: inline-block; font-size: 10px; background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 2px 6px; border-radius: 4px; margin-bottom: 2px;">
                                        ⚠️ {{ $s['discrepancy_count'] }} Selisih
                                    </span><br>
                                @else
                                    <span style="font-size: 10px; color: #34d399;">✓ Stok Pas</span><br>
                                @endif

                                @if($s['hardware_status'] !== 'normal')
                                    <span style="display: inline-block; font-size: 9.5px; background: rgba(245, 158, 11, 0.2); color: #fcd34d; padding: 1px 5px; border-radius: 3px;">
                                        HW: {{ strtoupper($s['hardware_status']) }}
                                    </span>
                                @else
                                    <span style="font-size: 9.5px; color: #94a3b8;">HW: 15 PC OK</span>
                                @endif

                                @if(!empty($s['deposit_proof_url']))
                                    <div class="no-print" style="margin-top: 3px;">
                                        <a href="{{ $s['deposit_proof_url'] }}" target="_blank" style="font-size: 9.5px; color: #38bdf8; text-decoration: underline;">
                                            📷 Bukti Setor
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" style="padding: 16px; text-align: center; color: #94a3b8;">
                                Belum ada rekapitulasi shift yang ditutup untuk tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background: #1e293b; font-weight: 800; border-top: 2px solid #475569; color: #f8fafc;">
                            <td colspan="2" style="padding: 10px 8px; text-transform: uppercase;">GRAND TOTAL (24 JAM)</td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace;">{{ number_format($zReportData['grand_total_billing'] ?? 0, 0, ',', '.') }}</td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace;">{{ number_format($zReportData['grand_total_fnb'] ?? 0, 0, ',', '.') }}</td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace;">{{ number_format($zReportData['grand_total_print'] ?? 0, 0, ',', '.') }}</td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; color: #38bdf8;">{{ number_format($zReportData['grand_total_revenue'] ?? 0, 0, ',', '.') }}</td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; color: #34d399;">{{ number_format($zReportData['grand_total_deposited'] ?? 0, 0, ',', '.') }}</td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace; color: #fbbf24;">{{ number_format($zReportData['final_retained_cash'] ?? 150000, 0, ',', '.') }}</td>
                            <td style="padding: 10px 8px; text-align: right; font-family: monospace;">{{ number_format($zReportData['grand_total_cash_difference'] ?? 0, 0, ',', '.') }}</td>
                            <td style="padding: 10px 8px; text-align: center; font-size: 10px;">
                                {{ ($zReportData['total_discrepancy_items'] ?? 0) > 0 ? '⚠️ Ada Selisih' : '✓ Bersih' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- CATATAN AUDIT & KELAYAKAN HARDWARE --}}
            <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid #334155; border-radius: 8px; padding: 12px 14px; margin-bottom: 20px; font-size: 11.5px;">
                <strong style="color: #cbd5e1;">📋 Catatan Audit & Kelayakan Operasional:</strong>
                <ul style="margin: 6px 0 0 18px; padding: 0; color: #94a3b8; line-height: 1.6;">
                    <li>Status Fisik Barang: <strong>{{ ($zReportData['total_discrepancy_items'] ?? 0) === 0 ? 'Semua stok minuman, makanan & kertas print akurat sesuai sistem.' : ($zReportData['total_discrepancy_items'] . ' item tercatat selisih fisik antar-shift.') }}</strong></li>
                    <li>Status Perangkat: <strong>{{ ($zReportData['total_hardware_issues'] ?? 0) === 0 ? '15 PC Station, Server Billing, dan Printer EPSON beroperasi normal tanpa kendala.' : 'Terdapat ' . $zReportData['total_hardware_issues'] . ' shift yang melaporkan catatan kendala hardware.' }}</strong></li>
                    <li>SOP Penutupan Kas: Kasir Shift 4 wajib mengamankan uang tunai hasil omset ke Brankas/Bank dan menyisakan modal laci <strong>Rp {{ number_format($zReportData['final_retained_cash'] ?? 150000, 0, ',', '.') }}</strong> untuk Kasir Shift 1.</li>
                </ul>
            </div>

            {{-- LEMBAR PENGESAHAN & TANDA TANGAN (SIGNATURE BLOCK) --}}
            <div style="margin-top: 24px; padding-top: 14px; border-top: 1px dashed #475569;">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: center; font-size: 11.5px;">
                    <div>
                        <div style="color: #94a3b8;">Kasir Penutup (Shift 4)</div>
                        <div style="height: 60px;"></div>
                        <div style="border-top: 1px solid #64748b; padding-top: 4px; font-weight: 700; color: #f8fafc;">
                            ( .................................... )
                        </div>
                        <div style="font-size: 10px; color: #64748b;">Yang Menyetor / Tutup Kasir</div>
                    </div>

                    <div>
                        <div style="color: #94a3b8;">Kasir Penerima (Shift 1 Besok)</div>
                        <div style="height: 60px;"></div>
                        <div style="border-top: 1px solid #64748b; padding-top: 4px; font-weight: 700; color: #f8fafc;">
                            ( .................................... )
                        </div>
                        <div style="font-size: 10px; color: #64748b;">Penerima Modal Laci Kasir</div>
                    </div>

                    <div>
                        <div style="color: #94a3b8;">Supervisor / Owner MGK</div>
                        <div style="height: 60px;"></div>
                        <div style="border-top: 1px solid #64748b; padding-top: 4px; font-weight: 700; color: #f8fafc;">
                            ( .................................... )
                        </div>
                        <div style="font-size: 10px; color: #64748b;">Verifikator & Manajemen</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- CSS KHUSUS MEDIA PRINT --}}
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #modal-daily-z-report,
    #modal-daily-z-report * {
        visibility: visible !important;
    }
    #modal-daily-z-report {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .modal-card {
        background: #ffffff !important;
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        color: #000000 !important;
    }
    #z-report-printable-area {
        color: #000000 !important;
        padding: 0 !important;
    }
    #z-report-printable-area * {
        color: #000000 !important;
        background: transparent !important;
        border-color: #94a3b8 !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<script>
function openDailyZReportModal() {
    const modal = document.getElementById('modal-daily-z-report');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeDailyZReportModal() {
    const modal = document.getElementById('modal-daily-z-report');
    if (modal) {
        modal.style.display = 'none';
    }
}

function printDailyZReport() {
    window.print();
}
</script>

