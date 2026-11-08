<!-- MODAL 1: CHECKLIST SERAH TERIMA SHIFT SEBELUMNYA -->
<div id="modal-prev-checklist" class="pos-modal-overlay" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 650px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); border: 1px solid #cbd5e1; font-family: 'Roboto', sans-serif;">
        <div style="padding: 16px 20px; background: #0284c7; color: #ffffff; border-top-left-radius: 11px; border-top-right-radius: 11px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">📋</span>
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; line-height: 1.2;">Checklist Serah Terima Shift Sebelumnya</h3>
                    <div style="font-size: 11.5px; opacity: 0.9; margin-top: 2px;">
                        Verifikasi kondisi fisik & stok dari kasir shift sebelum Anda bertugas
                    </div>
                </div>
            </div>
            <button type="button" onclick="closePrevChecklistModal()" style="background: rgba(255,255,255,0.2); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 14px; font-weight: bold;">✕</button>
        </div>

        <div style="padding: 20px; color: #334155;">
            @php
                $prevInfo = $prevShiftBilling ?? [];
                $prevChecklist = $prevInfo['checklist'] ?? null;
                $prevSummary = $prevInfo['summary'] ?? null;
            @endphp

            <!-- INFO SHIFT SEBELUMNYA -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
                <div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Operator Sebelumnya</div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;">
                        👤 {{ $prevInfo['operator_name'] ?? 'Kasir Shift Lalu' }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Shift & Jam Operasional</div>
                    <div style="font-size: 13px; font-weight: 600; color: #0284c7; margin-top: 2px;">
                        ⏰ {{ $prevInfo['name'] ?? 'Shift Sebelumnya' }} ({{ $prevInfo['time_range'] ?? '-' }})
                    </div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Tanggal Dinas</div>
                    <div style="font-size: 13px; font-weight: 600; color: #334155; margin-top: 2px;">
                        📅 {{ $prevInfo['work_date_formatted'] ?? ($prevInfo['work_date'] ?? date('d-m-Y')) }}
                    </div>
                </div>
            </div>

            <!-- RINCIAN FISIK CHECKLIST -->
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <!-- KERTAS PRINT -->
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>📄</span> Stok Kertas Print Diserahterimakan
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                        <div style="background: #f1f5f9; padding: 8px 12px; border-radius: 6px;">
                            <span style="color: #64748b;">Kertas A4:</span>
                            <strong style="color: #0f172a; float: right;">
                                {{ $prevChecklist->paper_a4_qty ?? 0 }} Lembar
                            </strong>
                        </div>
                        <div style="background: #f1f5f9; padding: 8px 12px; border-radius: 6px;">
                            <span style="color: #64748b;">Kertas F4 / Folio:</span>
                            <strong style="color: #0f172a; float: right;">
                                {{ $prevChecklist->paper_f4_qty ?? 0 }} Lembar
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- SHOWCASE MINUMAN & MAKANAN -->
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>🥤</span> Minuman Showcase & Makanan Etalase
                    </div>
                    @if(!empty($prevChecklist->drink_notes) || !empty($prevChecklist->food_notes))
                        <div style="font-size: 12px; background: #fefce8; border: 1px solid #fef08a; padding: 8px 12px; border-radius: 6px; color: #854d0e; line-height: 1.4;">
                            @if(!empty($prevChecklist->drink_notes))
                                <div><strong>Minuman:</strong> {{ $prevChecklist->drink_notes }}</div>
                            @endif
                            @if(!empty($prevChecklist->food_notes))
                                <div style="margin-top: 3px;"><strong>Makanan:</strong> {{ $prevChecklist->food_notes }}</div>
                            @endif
                        </div>
                    @else
                        <div style="font-size: 11.5px; color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 6px 10px; border-radius: 6px;">
                            ✓ Stok showcase minuman & makanan dilaporkan lengkap sesuai fisik etalase.
                        </div>
                    @endif
                </div>

                <!-- HARDWARE PC -->
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>🖥️</span> Kondisi Hardware PC Station
                    </div>
                    @php
                        $hwStatus = $prevChecklist->hardware_status ?? 'ok';
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; background: {{ $hwStatus === 'issue' ? '#fff1f2' : '#f0fdf4' }}; border: 1px solid {{ $hwStatus === 'issue' ? '#fecdd3' : '#bbf7d0' }}; padding: 8px 12px; border-radius: 6px;">
                        <span>Status PC Station:</span>
                        @if($hwStatus === 'issue')
                            <strong style="color: #be123c;">🔴 Ada Kendala / Butuh Maintenance</strong>
                        @else
                            <strong style="color: #166534;">🟢 Semua PC Berfungsi Normal (15 Unit)</strong>
                        @endif
                    </div>
                    @if(!empty($prevChecklist->hardware_notes))
                        <div style="font-size: 11.5px; color: #991b1b; margin-top: 6px; font-style: italic;">
                            Catatan: {{ $prevChecklist->hardware_notes }}
                        </div>
                    @endif
                </div>

                <!-- MODAL KAS LACI KASIR -->
                <div style="border: 2px solid #0284c7; border-radius: 8px; padding: 12px; background: #f0f9ff;">
                    <div style="font-size: 12px; font-weight: 700; color: #0369a1; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                        <span>💵 Modal Kas Ditinggalkan di Laci Kasir:</span>
                        <span style="font-size: 16px; font-weight: 800; color: #0369a1; font-family: monospace;">
                            Rp {{ number_format($prevInfo['retained_drawer_cash'] ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div style="font-size: 11.5px; color: #475569; line-height: 1.4;">
                        • Total Uang Disetor Shift Lalu: <strong>Rp {{ number_format($prevInfo['deposited_amount'] ?? 0, 0, ',', '.') }}</strong> (Metode: {{ $prevInfo['deposit_method_label'] ?? 'Estafet Kasir' }})<br>
                        • Catatan Serah Terima: <em>"{{ $prevInfo['handover_notes'] ?? 'Uang kas dan rekapan billing dalam kondisi normal.' }}"</em>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closePrevChecklistModal()" style="padding: 7px 18px; background: #0284c7; color: #fff; border: none; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer;">
                Tutup Checklist
            </button>
        </div>
    </div>
</div>

<!-- MODAL 2: RINCIAN PENJUALAN SHIFT SEBELUMNYA -->
<div id="modal-prev-sales" class="pos-modal-overlay" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 750px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); border: 1px solid #cbd5e1; font-family: 'Roboto', sans-serif;">
        <div style="padding: 16px 20px; background: #4338ca; color: #ffffff; border-top-left-radius: 11px; border-top-right-radius: 11px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">🔍</span>
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; line-height: 1.2;">Riwayat & Rincian Penjualan Shift Sebelumnya</h3>
                    <div style="font-size: 11.5px; opacity: 0.9; margin-top: 2px;">
                        Data omset, item terjual, dan status transaksi kasir sebelum Anda
                    </div>
                </div>
            </div>
            <button type="button" onclick="closePrevSalesModal()" style="background: rgba(255,255,255,0.2); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 14px; font-weight: bold;">✕</button>
        </div>

        <div style="padding: 20px; color: #334155;">
            <!-- KPI CARDS SHIFT LALU -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 16px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Omset Billing Warnet</div>
                    <div style="font-size: 16px; font-weight: 800; color: #f59e0b; margin-top: 2px; font-family: monospace;">
                        Rp {{ number_format($prevInfo['billing_total'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Omset F&B Kasir</div>
                    <div style="font-size: 16px; font-weight: 800; color: #10b981; margin-top: 2px; font-family: monospace;">
                        Rp {{ number_format($prevInfo['fnb_total'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Cetak Print Kertas</div>
                    <div style="font-size: 16px; font-weight: 800; color: #0284c7; margin-top: 2px; font-family: monospace;">
                        Rp {{ number_format($prevInfo['print_total'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 10px 12px;">
                    <div style="font-size: 11px; color: #4338ca; font-weight: 700; text-transform: uppercase;">Total Omset Shift Lalu</div>
                    <div style="font-size: 18px; font-weight: 900; color: #3730a3; margin-top: 2px; font-family: monospace;">
                        Rp {{ number_format($prevInfo['grand_total'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- TABEL TRANSAKSI PENJUALAN SHIFT SEBELUMNYA -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                <div style="background: #f8fafc; padding: 8px 12px; font-size: 12px; font-weight: 700; color: #334155; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between;">
                    <span>Daftar Transaksi Kasir (Total: {{ count($prevInfo['sales_list'] ?? []) }} Nota)</span>
                    <span style="color: #64748b; font-weight: normal;">Shift {{ $prevInfo['name'] ?? '' }}</span>
                </div>
                <div style="max-height: 280px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; text-align: left;">
                        <thead>
                            <tr style="background: #f1f5f9; color: #475569; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 6px 10px;">Jam</th>
                                <th style="padding: 6px 10px;">No. Invoice</th>
                                <th style="padding: 6px 10px;">Rincian Produk</th>
                                <th style="padding: 6px 10px; text-align: right;">Total</th>
                                <th style="padding: 6px 10px; text-align: center;">Metode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($prevInfo['sales_list'] ?? []) as $pSale)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 6px 10px; color: #64748b;">
                                        {{ $pSale->created_at ? $pSale->created_at->format('H:i') : '-' }}
                                    </td>
                                    <td style="padding: 6px 10px; font-family: monospace; font-weight: 600; color: #0284c7;">
                                        {{ $pSale->invoice_number }}
                                    </td>
                                    <td style="padding: 6px 10px;">
                                        @foreach($pSale->items as $it)
                                            <div>{{ $it->qty }}x {{ $it->item_name }}</div>
                                        @endforeach
                                    </td>
                                    <td style="padding: 6px 10px; text-align: right; font-weight: 700; color: #0f172a;">
                                        Rp {{ number_format((float) ($pSale->net_amount > 0 ? $pSale->net_amount : $pSale->total_fnb), 0, ',', '.') }}
                                    </td>
                                    <td style="padding: 6px 10px; text-align: center;">
                                        <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; background: {{ $pSale->payment_method === 'cash' ? '#dcfce7' : '#eff6ff' }}; color: {{ $pSale->payment_method === 'cash' ? '#166534' : '#1e40af' }};">
                                            {{ strtoupper($pSale->payment_method) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 20px; text-align: center; color: #94a3b8;">
                                        Tidak ada transaksi POS yang tercatat pada shift sebelumnya.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closePrevSalesModal()" style="padding: 7px 18px; background: #4338ca; color: #fff; border: none; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer;">
                Tutup Riwayat Penjualan
            </button>
        </div>
    </div>
</div>

<!-- MODAL 3: CLOSING STATEMENT & SETTLEMENT SETORAN HARIAN -->
<div id="modal-daily-settlement" class="pos-modal-overlay" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); border: 1px solid #cbd5e1; font-family: 'Roboto', sans-serif;">
        <div style="padding: 16px 20px; background: #0f766e; color: #ffffff; border-top-left-radius: 11px; border-top-right-radius: 11px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;">📊</span>
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; line-height: 1.2;">Closing Statement & Settlement Setoran Harian</h3>
                    <div style="font-size: 11.5px; opacity: 0.9; margin-top: 2px;">
                        Rekapitulasi shift harian dan status setor kasir di akhir hari
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeDailySettlementModal()" style="background: rgba(255,255,255,0.2); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 14px; font-weight: bold;">✕</button>
        </div>

        <div style="padding: 20px; color: #334155;">
            @php
                $ds = $dailySettlement ?? [];
                $shiftsList = $ds['shifts'] ?? [];
            @endphp

            <!-- STATUS HARIAN RINGKASAN -->
            <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #0f766e; text-transform: uppercase;">Progres Settlement Harian</div>
                    <div style="font-size: 18px; font-weight: 800; color: #134e4a; margin-top: 2px;">
                        {{ $ds['settled_count'] ?? 0 }} dari {{ $ds['total_shift_count'] ?? 4 }} Shift Telah Rekap & Setor
                    </div>
                </div>
                <div>
                    @if(!empty($ds['all_settled']))
                        <span style="background: #10b981; color: #fff; padding: 6px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 800;">
                            ✓ HARI INI LENGKAP SETTLED
                        </span>
                    @else
                        <span style="background: #f59e0b; color: #fff; padding: 6px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 800;">
                            ⏳ MENUNGGU SHIFT BERJALAN
                        </span>
                    @endif
                </div>
            </div>

            <!-- RINCIAN STATUS PER-SHIFT -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 16px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc; color: #475569; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 8px 12px;">Shift</th>
                            <th style="padding: 8px 12px;">Jam Operasional</th>
                            <th style="padding: 8px 12px; text-align: right;">Omset Tercatat</th>
                            <th style="padding: 8px 12px; text-align: center;">Status Settlement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shiftsList as $sh)
                            <tr style="border-bottom: 1px solid #f1f5f9; {{ ($sh['is_current'] ?? false) ? 'background: #f0fdf4;' : '' }}">
                                <td style="padding: 10px 12px; font-weight: 700; color: #0f172a;">
                                    {{ $sh['name'] ?? 'Shift' }}
                                    @if($sh['is_current'] ?? false)
                                        <span style="font-size: 10px; background: #22c55e; color: #fff; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">Shift Aktif Anda</span>
                                    @endif
                                </td>
                                <td style="padding: 10px 12px; color: #64748b;">
                                    {{ substr((string) ($sh['shift']->start_time ?? '00:00'), 0, 5) }} - {{ substr((string) ($sh['shift']->end_time ?? '00:00'), 0, 5) }}
                                </td>
                                <td style="padding: 10px 12px; text-align: right; font-weight: 800; font-family: monospace; color: #0f172a;">
                                    Rp {{ number_format((float) ($sh['sales'] ?? 0), 0, ',', '.') }}
                                </td>
                                <td style="padding: 10px 12px; text-align: center;">
                                    <span style="font-size: 11px; font-weight: 700; color: {{ $sh['status_color'] ?? '#64748b' }};">
                                        {{ $sh['status_label'] ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- CLOSING STATEMENT AKHIR HARI -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                <div style="font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
                    📝 Ringkasan Closing Statement Akumulatif Harian
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                    <div>• Total Estimasi Omset Harian:</div>
                    <div style="text-align: right; font-weight: 700; font-family: monospace;">
                        Rp {{ number_format((float) ($ds['total_billing'] ?? 0), 0, ',', '.') }}
                    </div>
                    <div>• Total Kas Telah Disetor:</div>
                    <div style="text-align: right; font-weight: 700; font-family: monospace; color: #10b981;">
                        Rp {{ number_format((float) ($ds['total_settled_cash'] ?? 0), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <a href="{{ route('presensi2.closing.print', ['date' => isset($workDate) ? (is_object($workDate) ? $workDate->toDateString() : (string)$workDate) : date('Y-m-d')]) }}" target="_blank" style="padding: 7px 16px; background: #0284c7; color: #fff; border: none; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;" title="Cetak Berita Acara Rekonsiliasi & Settlement Kas Harian">
                <span>🖨️</span> Cetak Berita Acara Closing (BA)
            </a>
            <button type="button" onclick="closeDailySettlementModal()" style="padding: 7px 18px; background: #0f766e; color: #fff; border: none; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer;">
                Tutup Settlement
            </button>
        </div>
    </div>
</div>

<script>
function openPrevChecklistModal() {
    const m = document.getElementById('modal-prev-checklist');
    if (m) m.style.display = 'flex';
}
function closePrevChecklistModal() {
    const m = document.getElementById('modal-prev-checklist');
    if (m) m.style.display = 'none';
}

function openPrevSalesModal() {
    const m = document.getElementById('modal-prev-sales');
    if (m) m.style.display = 'flex';
}
function closePrevSalesModal() {
    const m = document.getElementById('modal-prev-sales');
    if (m) m.style.display = 'none';
}

function openDailySettlementModal() {
    const m = document.getElementById('modal-daily-settlement');
    if (m) m.style.display = 'flex';
}
function closeDailySettlementModal() {
    const m = document.getElementById('modal-daily-settlement');
    if (m) m.style.display = 'none';
}
</script>

