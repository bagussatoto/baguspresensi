@extends('admin.dashboard.layout')

@section('title', 'Persetujuan Penyesuaian Billing & Print Kasir')

@section('content')
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">⚡</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">Persetujuan Penyesuaian Billing Kasir & Print Kertas</h1>
                <span class="section-subtitle">Dual Control Anti-Fraud — kompensasi gangguan baru memotong target setor kasir setelah disetujui Admin</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @php
                $totalPending = $pendingAdjustments->count() + $pendingPrintAdjustments->count();
            @endphp
            <span class="badge-status {{ $totalPending > 0 ? 'badge-amber' : 'badge-emerald' }}">
                {{ $totalPending }} Menunggu Persetujuan
            </span>
            <a href="{{ route('admin.penjualan') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                🛒 Penjualan
            </a>
        </div>
    </div>

    <div style="padding: 20px 24px 0;">
        {{-- STAT CARDS --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
            <div style="background: rgba(245, 158, 11, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #fbbf24; font-weight: 700;">⚡ Billing Internet Pending</div>
                <div style="font-size: 24px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin-top: 4px;">{{ $pendingAdjustments->count() }} Pengajuan</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">
                    Total: Rp {{ number_format((float) $pendingAdjustments->sum('billing_adjustment_amount'), 0, ',', '.') }}
                </div>
            </div>
            <div style="background: rgba(2, 132, 199, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(2, 132, 199, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 700;">🖨️ Print Kertas Pending</div>
                <div style="font-size: 24px; font-weight: 800; color: #38bdf8; font-family: 'Outfit'; margin-top: 4px;">{{ $pendingPrintAdjustments->count() }} Pengajuan</div>
                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">
                    Total: {{ (int) $pendingPrintAdjustments->sum('print_adjustment_pages') }} lbr (Rp {{ number_format((float) $pendingPrintAdjustments->sum('print_adjustment_amount'), 0, ',', '.') }})
                </div>
            </div>
            <div style="background: rgba(16, 185, 129, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">✅ Total Disetujui</div>
                <div style="font-size: 24px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin-top: 4px;">
                    {{ $processedAdjustments->where('billing_adjustment_status', 'approved')->count() + $processedPrintAdjustments->where('print_adjustment_status', 'approved')->count() }} Pengajuan
                </div>
            </div>
            <div style="background: rgba(244, 63, 94, 0.08); padding: 16px; border-radius: 12px; border: 1px solid rgba(244, 63, 94, 0.25);">
                <div style="font-size: 11px; text-transform: uppercase; color: #f43f5e; font-weight: 700;">❌ Total Ditolak</div>
                <div style="font-size: 24px; font-weight: 800; color: #f43f5e; font-family: 'Outfit'; margin-top: 4px;">
                    {{ $processedAdjustments->where('billing_adjustment_status', 'rejected')->count() + $processedPrintAdjustments->where('print_adjustment_status', 'rejected')->count() }} Pengajuan
                </div>
            </div>
        </div>

        @if(session('success'))
            <div style="margin-top: 16px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; border-radius: 10px; padding: 12px 16px; color: #34d399; font-size: 13px;">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="margin-top: 16px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 10px; padding: 12px 16px; color: #fca5a5; font-size: 13px;">
                ⚠️ {{ session('error') }}
            </div>
        @endif
        @if(isset($errors) && $errors->any())
            <div style="margin-top: 16px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 10px; padding: 12px 16px; color: #fca5a5; font-size: 13px;">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- BAGIAN 1: ANTREAN PENYESUAIAN BILLING -->
    <!-- ========================================== -->
    <div style="padding: 24px 24px 12px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #fbbf24; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
            <span>⚡</span> 1. Antrean Penyesuaian Billing Internet Shift
        </h3>
        <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 14px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02);">
                        <th style="padding: 12px 16px; color: var(--text-sub);">Tanggal / Shift</th>
                        <th style="padding: 12px 16px; color: var(--text-sub);">Kasir / Cabang</th>
                        <th style="padding: 12px 16px; color: var(--text-sub);">Alasan &amp; Berita Acara</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Bruto</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Potongan</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Riil Jika Disetujui</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingAdjustments as $adj)
                        @php
                            $rawAmt = (float) ($adj->raw_billing_amount ?? $adj->locked_billing_amount ?? 0);
                            $dedAmt = (float) $adj->billing_adjustment_amount;
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: #fff;">{{ $adj->work_date?->format('d M Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-sub);">{{ $adj->shift?->name ?? 'Shift' }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 700; color: #38bdf8;">{{ $adj->employee?->name ?? '-' }}</div>
                                <div style="font-size: 11px; color: var(--text-sub);">{{ $adj->branch?->name ?? '-' }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; max-width: 320px;">
                                <div style="font-weight: 600; color: #fbbf24;">{{ $adj->billingAdjustmentReasonLabel() }}</div>
                                <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">
                                    @if($adj->billing_blackout_minutes) Durasi {{ $adj->billing_blackout_minutes }} mnt @endif
                                    @if($adj->billing_blackout_pcs) &bull; {{ $adj->billing_blackout_pcs }} PC @endif
                                </div>
                                <div style="font-size: 12px; color: #cbd5e1; margin-top: 4px; font-style: italic;">"{{ $adj->billing_adjustment_notes }}"</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right; font-family: monospace; color: #fff;">Rp {{ number_format($rawAmt, 0, ',', '.') }}</td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right; font-family: monospace; color: #f87171; font-weight: 700;">-Rp {{ number_format($dedAmt, 0, ',', '.') }}</td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right; font-family: monospace; color: #34d399; font-weight: 700;">Rp {{ number_format(max(0, $rawAmt - $dedAmt), 0, ',', '.') }}</td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right;">
                                <div style="display: inline-flex; flex-direction: column; gap: 8px; align-items: stretch; min-width: 210px;">
                                    <form method="POST" action="{{ route('admin.penjualan.penyesuaian-billing.approve', $adj->id) }}" onsubmit="return confirm('Setujui potongan billing Rp {{ number_format($dedAmt, 0, ',', '.') }} untuk kasir {{ $adj->employee?->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn-action-sm" style="width: 100%; background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-weight: 700;">
                                            ✅ Setujui Billing
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.penjualan.penyesuaian-billing.reject', $adj->id) }}" onsubmit="return confirm('Tolak pengajuan billing ini? Kasir wajib menyetor omset penuh.');" style="display: flex; flex-direction: column; gap: 6px;">
                                        @csrf
                                        <input type="text" name="rejection_reason" required maxlength="500" placeholder="Alasan penolakan (wajib)"
                                               style="padding: 6px 10px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.4); background: rgba(15, 23, 42, 0.8); color: #fff; font-size: 12px;">
                                        <button type="submit" class="btn-action-sm" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-weight: 700;">
                                            ❌ Tolak Billing
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 24px 16px; text-align: center; color: var(--text-sub);">
                                Tidak ada pengajuan billing yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- BAGIAN 2: ANTREAN PENYESUAIAN PRINT KERTAS -->
    <!-- ========================================== -->
    <div style="padding: 12px 24px 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #38bdf8; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
            <span>🖨️</span> 2. Antrean Penyesuaian Print Kertas Shift (Kertas Macet / Tinta Rusak / Toko)
        </h3>
        <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 14px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02);">
                        <th style="padding: 12px 16px; color: var(--text-sub);">Tanggal / Shift</th>
                        <th style="padding: 12px 16px; color: var(--text-sub);">Kasir / Cabang</th>
                        <th style="padding: 12px 16px; color: var(--text-sub);">Alasan &amp; Kronologi</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Sistem Asli</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Potongan Rusak</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Target Bersih</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingPrintAdjustments as $adj)
                        @php
                            $rawPages = (int) ($adj->raw_print_pages ?? $adj->locked_print_pages ?? 0);
                            $rawAmt = (float) ($adj->raw_print_amount ?? $adj->locked_print_amount ?? 0);
                            $dedPages = (int) $adj->print_adjustment_pages;
                            $dedAmt = (float) $adj->print_adjustment_amount;
                            $netPages = max(0, $rawPages - $dedPages);
                            $netAmt = max(0, $rawAmt - $dedAmt);
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: #fff;">{{ $adj->work_date?->format('d M Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-sub);">{{ $adj->shift?->name ?? 'Shift' }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 700; color: #38bdf8;">{{ $adj->employee?->name ?? '-' }}</div>
                                <div style="font-size: 11px; color: var(--text-sub);">{{ $adj->branch?->name ?? '-' }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; max-width: 320px;">
                                <div style="font-weight: 600; color: #38bdf8;">{{ $adj->printAdjustmentReasonLabel() }}</div>
                                <div style="font-size: 12px; color: #cbd5e1; margin-top: 4px; font-style: italic;">"{{ $adj->print_adjustment_notes }}"</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right; font-family: monospace; color: #fff;">
                                <div>{{ $rawPages }} lbr</div>
                                <div style="font-size: 11px; color: var(--text-sub);">Rp {{ number_format($rawAmt, 0, ',', '.') }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right; font-family: monospace; color: #f87171; font-weight: 700;">
                                <div>-{{ $dedPages }} lbr</div>
                                <div style="font-size: 11px;">-Rp {{ number_format($dedAmt, 0, ',', '.') }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right; font-family: monospace; color: #34d399; font-weight: 700;">
                                <div>{{ $netPages }} lbr</div>
                                <div style="font-size: 11px;">Rp {{ number_format($netAmt, 0, ',', '.') }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right;">
                                <div style="display: inline-flex; flex-direction: column; gap: 8px; align-items: stretch; min-width: 210px;">
                                    <form method="POST" action="{{ route('admin.penjualan.penyesuaian-print.approve', $adj->id) }}" onsubmit="return confirm('Setujui kompensasi print {{ $dedPages }} lbr (Rp {{ number_format($dedAmt, 0, ',', '.') }}) untuk kasir {{ $adj->employee?->name }}?');">
                                        @csrf
                                        <button type="submit" class="btn-action-sm" style="width: 100%; background: rgba(2, 132, 199, 0.2); border: 1px solid #0284c7; color: #38bdf8; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-weight: 700;">
                                            ✅ Setujui Print
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.penjualan.penyesuaian-print.reject', $adj->id) }}" onsubmit="return confirm('Tolak pengajuan print ini? Kasir wajib menyetor omset penuh.');" style="display: flex; flex-direction: column; gap: 6px;">
                                        @csrf
                                        <input type="text" name="rejection_reason" required maxlength="500" placeholder="Alasan penolakan (wajib)"
                                               style="padding: 6px 10px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.4); background: rgba(15, 23, 42, 0.8); color: #fff; font-size: 12px;">
                                        <button type="submit" class="btn-action-sm" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-weight: 700;">
                                            ❌ Tolak Print
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 24px 16px; text-align: center; color: var(--text-sub);">
                                Tidak ada pengajuan print kertas yang menunggu persetujuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- BAGIAN 3: RIWAYAT KEPUTUSAN (AUDIT TRAIL) -->
    <!-- ========================================== -->
    <div style="padding: 0 24px 24px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <span>📜</span> 3. Riwayat Keputusan Otorisasi (Audit Trail)
        </h3>
        <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 14px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12.5px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02);">
                        <th style="padding: 10px 14px; color: var(--text-sub);">Kategori</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Tanggal / Shift</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Kasir / Cabang</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Alasan &amp; Bukti</th>
                        <th style="padding: 10px 14px; color: var(--text-sub); text-align: right;">Potongan</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Status</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Diputuskan Oleh</th>
                        <th style="padding: 10px 14px; color: var(--text-sub);">Catatan Penolakan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $hasHistory = false;
                    @endphp
                    {{-- RIWAYAT BILLING --}}
                    @foreach($processedAdjustments as $adj)
                        @php
                            $hasHistory = true;
                            $isApprovedAdj = $adj->billing_adjustment_status === 'approved';
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                            <td style="padding: 10px 14px;">
                                <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: rgba(245, 158, 11, 0.15); color: #fbbf24;">⚡ BILLING</span>
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-sub);">
                                {{ $adj->work_date?->format('d M Y') }} &bull; {{ $adj->shift?->name ?? 'Shift' }}
                            </td>
                            <td style="padding: 10px 14px;">
                                <span style="font-weight: 600; color: #fff;">{{ $adj->employee?->name ?? '-' }}</span>
                                <span style="font-size: 11px; color: var(--text-sub);">({{ $adj->branch?->name ?? '-' }})</span>
                            </td>
                            <td style="padding: 10px 14px; color: #cbd5e1;">{{ $adj->billingAdjustmentReasonLabel() }}</td>
                            <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: {{ $isApprovedAdj ? '#f87171' : '#94a3b8' }}; {{ $isApprovedAdj ? '' : 'text-decoration: line-through;' }}">
                                -Rp {{ number_format((float) $adj->billing_adjustment_amount, 0, ',', '.') }}
                            </td>
                            <td style="padding: 10px 14px;">
                                <span class="badge-status {{ $isApprovedAdj ? 'badge-emerald' : 'badge-rose' }}">
                                    {{ $isApprovedAdj ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            </td>
                            <td style="padding: 10px 14px; color: #cbd5e1;">
                                @if($isApprovedAdj)
                                    {{ $adj->billing_adjustment_approved_by }}
                                    <div style="font-size: 11px; color: var(--text-sub);">{{ $adj->billing_adjustment_approved_at?->format('d M Y, H:i') }}</div>
                                @else
                                    {{ $adj->billing_adjustment_rejected_by }}
                                    <div style="font-size: 11px; color: var(--text-sub);">{{ $adj->billing_adjustment_rejected_at?->format('d M Y, H:i') }}</div>
                                @endif
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-sub);">{{ $isApprovedAdj ? '-' : $adj->billing_adjustment_rejection_reason }}</td>
                        </tr>
                    @endforeach

                    {{-- RIWAYAT PRINT --}}
                    @foreach($processedPrintAdjustments as $adj)
                        @php
                            $hasHistory = true;
                            $isApprovedPrint = $adj->print_adjustment_status === 'approved';
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                            <td style="padding: 10px 14px;">
                                <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: rgba(2, 132, 199, 0.15); color: #38bdf8;">🖨️ PRINT</span>
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-sub);">
                                {{ $adj->work_date?->format('d M Y') }} &bull; {{ $adj->shift?->name ?? 'Shift' }}
                            </td>
                            <td style="padding: 10px 14px;">
                                <span style="font-weight: 600; color: #fff;">{{ $adj->employee?->name ?? '-' }}</span>
                                <span style="font-size: 11px; color: var(--text-sub);">({{ $adj->branch?->name ?? '-' }})</span>
                            </td>
                            <td style="padding: 10px 14px; color: #cbd5e1;">
                                <div>{{ $adj->printAdjustmentReasonLabel() }}</div>
                                <div style="font-size: 11px; color: var(--text-sub); font-style: italic;">"{{ $adj->print_adjustment_notes }}"</div>
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-family: monospace; color: {{ $isApprovedPrint ? '#f87171' : '#94a3b8' }}; {{ $isApprovedPrint ? '' : 'text-decoration: line-through;' }}">
                                -{{ $adj->print_adjustment_pages }} lbr (-Rp {{ number_format((float) $adj->print_adjustment_amount, 0, ',', '.') }})
                            </td>
                            <td style="padding: 10px 14px;">
                                <span class="badge-status {{ $isApprovedPrint ? 'badge-emerald' : 'badge-rose' }}">
                                    {{ $isApprovedPrint ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            </td>
                            <td style="padding: 10px 14px; color: #cbd5e1;">
                                @if($isApprovedPrint)
                                    {{ $adj->print_adjustment_approved_by }}
                                    <div style="font-size: 11px; color: var(--text-sub);">{{ $adj->print_adjustment_approved_at?->format('d M Y, H:i') }}</div>
                                @else
                                    {{ $adj->print_adjustment_rejected_by }}
                                    <div style="font-size: 11px; color: var(--text-sub);">{{ $adj->print_adjustment_rejected_at?->format('d M Y, H:i') }}</div>
                                @endif
                            </td>
                            <td style="padding: 10px 14px; color: var(--text-sub);">{{ $isApprovedPrint ? '-' : $adj->print_adjustment_rejection_reason }}</td>
                        </tr>
                    @endforeach

                    @if(! $hasHistory)
                        <tr>
                            <td colspan="8" style="padding: 24px 16px; text-align: center; color: var(--text-sub);">Belum ada riwayat keputusan otorisasi.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
