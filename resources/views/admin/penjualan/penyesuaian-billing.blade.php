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

    <!-- ========================================== -->
    <!-- BAGIAN 4: BUKA KUNCI PENJUALAN SHIFT POS -->
    <!-- ========================================== -->
    <div style="padding: 12px 24px 28px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #f43f5e; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
            <span>🔓</span> 4. Buka Kunci Penjualan Shift (Re-Open Shift Terkunci)
        </h3>
        <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 14px;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.02);">
                        <th style="padding: 12px 16px; color: var(--text-sub);">Tanggal / Shift</th>
                        <th style="padding: 12px 16px; color: var(--text-sub);">Kasir / Cabang</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Total Omset</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: right;">Selisih Kas Laci</th>
                        <th style="padding: 12px 16px; color: var(--text-sub);">Status Selisih</th>
                        <th style="padding: 12px 16px; color: var(--text-sub);">Status Kunci</th>
                        <th style="padding: 12px 16px; color: var(--text-sub); text-align: center;">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lockedShiftSummaries ?? [] as $lockedSummary)
                        @php
                            $cashDiff = (float) ($lockedSummary->cash_difference ?? 0);
                        @endphp
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.04);">
                            <td style="padding: 10px 16px; color: #fff; font-weight: 600;">
                                {{ $lockedSummary->work_date?->format('d M Y') }} &bull; {{ $lockedSummary->shift?->name ?? 'Shift #'.$lockedSummary->shift_id }}
                            </td>
                            <td style="padding: 10px 16px;">
                                <span style="color: #fff; font-weight: 600;">{{ $lockedSummary->employee?->name ?? '-' }}</span>
                                <span style="font-size: 11px; color: var(--text-sub);">({{ $lockedSummary->branch?->name ?? '-' }})</span>
                            </td>
                            <td style="padding: 10px 16px; text-align: right; font-family: monospace; color: #34d399; font-weight: 700;">
                                Rp {{ number_format((float) $lockedSummary->total_revenue, 0, ',', '.') }}
                            </td>
                            <td style="padding: 10px 16px; text-align: right; font-family: monospace; font-weight: 700;">
                                @if(abs($cashDiff) < 1)
                                    <span class="badge-status badge-emerald">✓ Pas (Rp 0)</span>
                                @elseif($cashDiff < 0)
                                    <span class="badge-status badge-rose">⚠️ Kurang -Rp {{ number_format(abs($cashDiff), 0, ',', '.') }}</span>
                                @else
                                    <span class="badge-status badge-cyan">+Rp {{ number_format($cashDiff, 0, ',', '.') }} (Lebih)</span>
                                @endif
                            </td>
                            <td style="padding: 10px 16px;">
                                @if($lockedSummary->isDiscrepancyResolved())
                                    <span class="badge-status badge-emerald" title="{{ $lockedSummary->discrepancy_resolution_notes }}">
                                        ✅ {{ $lockedSummary->discrepancyActionLabel() }}
                                    </span>
                                    <div style="font-size: 10px; color: var(--text-sub); margin-top: 2px;">
                                        Oleh: {{ $lockedSummary->discrepancy_resolved_by }}
                                    </div>
                                @elseif(abs($cashDiff) > 1)
                                    <span class="badge-status badge-amber">⏳ Perlu Tindak Lanjut</span>
                                @else
                                    <span style="color: var(--text-sub); font-size: 11px;">-</span>
                                @endif
                            </td>
                            <td style="padding: 10px 16px;">
                                <span class="badge-status badge-rose">🔒 Terkunci ({{ strtoupper((string) $lockedSummary->status) }})</span>
                            </td>
                            <td style="padding: 10px 16px; text-align: center;">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    @if(abs($cashDiff) > 1 && ! $lockedSummary->isDiscrepancyResolved())
                                        <button type="button" onclick="openDiscrepancyModal({{ $lockedSummary->id }}, '{{ $lockedSummary->employee?->name ?? 'Kasir' }}', {{ (int) $cashDiff }})" class="btn-action-sm" style="background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.5); color: #fbbf24; font-weight: 700; cursor: pointer;">
                                            ⚖️ Selesaikan Selisih
                                        </button>
                                    @endif

                                    @php
                                        $isDeposited = $lockedSummary->isDepositedToBank();
                                        $isPast24h = $lockedSummary->isPastUnlockTimeLimit(24);
                                        $userRole = strtolower((string) ($profileData['role'] ?? session('admin_role', '')));
                                        $canOverride = in_array($userRole, ['super admin', 'super_admin', 'owner', 'manager']);
                                    @endphp

                                    @if($isDeposited)
                                        <span class="badge-status" style="background: rgba(100, 116, 139, 0.2); border: 1px solid rgba(100, 116, 139, 0.4); color: #94a3b8; font-size: 11px; padding: 4px 8px;" title="Kas shift telah disetor ke bank (Ref: {{ $lockedSummary->deposit_receipt_no ?: '-' }}). Rekap terkunci permanen demi integritas laporan keuangan.">
                                            🔒 Kas Disetor ({{ $lockedSummary->deposit_receipt_no ?: 'Bank' }})
                                        </span>
                                    @elseif($isPast24h)
                                        @if($canOverride)
                                            <button type="button" onclick="openOverrideUnlockModal({{ $lockedSummary->id }}, '{{ $lockedSummary->employee?->name ?? 'Kasir' }}', '{{ $lockedSummary->shift?->name ?? 'Shift' }}')" class="btn-action-sm" style="background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.5); color: #fbbf24; font-weight: 700; cursor: pointer;" title="Shift telah lewat batas 24 jam. Buka kunci memerlukan catatan alasan otorisasi Super Admin.">
                                                ⚠️ Override Buka (>24 Jam)
                                            </button>
                                        @else
                                            <span class="badge-status" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; font-size: 11px; padding: 4px 8px;" title="Shift telah terkunci lebih dari 24 jam. Pembukaan kunci hanya dapat dilakukan oleh Super Admin / Owner.">
                                                ⌛ Terkunci >24 Jam
                                            </span>
                                        @endif
                                    @else
                                        <form method="POST" action="{{ route('admin.penjualan.unlock-sales', $lockedSummary->id) }}" onsubmit="return confirm('Buka kembali kunci penjualan untuk shift ini agar kasir dapat menginput transaksi lagi?');" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn-action-sm" style="background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.5); color: #34d399; font-weight: 700; cursor: pointer;">
                                                🔓 Buka Kunci Shift
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 20px 16px; text-align: center; color: var(--text-sub);">Tidak ada shift penjualan yang sedang terkunci.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL RESOLUSI SELISIH KAS & STOK SHIFT --}}
<div id="modal-discrepancy-resolution" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div style="background: #1e293b; border: 1px solid var(--border-color); border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden;">
        <div style="padding: 16px 20px; background: rgba(245, 158, 11, 0.15); border-bottom: 1px solid rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">⚖️</span>
                <span style="font-weight: 700; color: #fbbf24; font-size: 14px;">Tindak Lanjut & Otorisasi Selisih Kas Laci</span>
            </div>
            <button type="button" onclick="closeDiscrepancyModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">✕</button>
        </div>
        <form id="form-discrepancy-resolution" method="POST" style="padding: 20px;">
            @csrf
            <div style="margin-bottom: 16px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 12px 14px;">
                <div style="font-size: 12px; color: var(--text-sub);">Kasir Shift: <strong id="modal-disc-kasir" style="color: #fff;">-</strong></div>
                <div style="font-size: 12px; color: var(--text-sub); margin-top: 4px;">Nominal Selisih Kas: <strong id="modal-disc-nominal" style="font-family: monospace; font-size: 13px;">-</strong></div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #e2e8f0; margin-bottom: 6px;">
                    Keputusan Tindak Lanjut Admin *
                </label>
                <select name="action" required style="width: 100%; background: #0f172a; border: 1px solid var(--border-color); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px;">
                    <option value="reimbursed_by_cashier">💵 Diganti Tunai Langsung oleh Kasir</option>
                    <option value="payroll_deduction">💼 Potong Gaji / Masuk Kasbon Karyawan</option>
                    <option value="expense_write_off">📉 Toleransi / Beban Kerugian Selisih Kas Outlet</option>
                    <option value="stock_reconciliation">📦 Penyesuaian Saldo Stok Fisik SAK</option>
                </select>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #e2e8f0; margin-bottom: 6px;">
                    Catatan / Alasan Keputusan (Opsional)
                </label>
                <textarea name="notes" rows="3" placeholder="Contoh: Kasir telah mengganti uang tunai Rp 10.000 ke laci kasir saat pergantian shift..." style="width: 100%; background: #0f172a; border: 1px solid var(--border-color); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 12px; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button type="button" onclick="closeDiscrepancyModal()" class="btn-action-sm" style="background: rgba(255, 255, 255, 0.05); color: #94a3b8; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-action-sm" style="background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.5); color: #fbbf24; font-weight: 700; cursor: pointer;">
                    ✓ Simpan Keputusan Selisih
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL OVERRIDE BUKA KUNCI SHIFT > 24 JAM --}}
<div id="modal-override-unlock" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div style="background: #1e293b; border: 1px solid var(--border-color); border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); overflow: hidden;">
        <div style="padding: 16px 20px; background: rgba(245, 158, 11, 0.15); border-bottom: 1px solid rgba(245, 158, 11, 0.3); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">⚠️</span>
                <span style="font-weight: 700; color: #fbbf24; font-size: 14px;">Otorisasi Khusus Buka Kunci (>24 Jam)</span>
            </div>
            <button type="button" onclick="closeOverrideUnlockModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">✕</button>
        </div>
        <form id="form-override-unlock" method="POST" style="padding: 20px;">
            @csrf
            <div style="font-size: 13px; color: #e2e8f0; margin-bottom: 14px; line-height: 1.5;">
                Shift ini telah terkunci lebih dari 24 jam. Sebagai <strong>Super Admin / Owner</strong>, Anda diwajibkan menyertakan alasan pembukaan kunci untuk keperluan kepatuhan audit ISO 27001.
            </div>
            <div style="background: rgba(15, 23, 42, 0.6); padding: 12px; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.05); margin-bottom: 16px; font-size: 12px;">
                <div style="color: var(--text-sub);">Kasir: <strong id="override-employee-name" style="color: #fff;">-</strong></div>
                <div style="color: var(--text-sub); margin-top: 4px;">Shift: <strong id="override-shift-name" style="color: #fff;">-</strong></div>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Catatan Alasan Otorisasi (Wajib):</label>
                <textarea name="reason" id="override-reason" rows="3" required placeholder="Contoh: Koreksi transaksi yang terlewat atas persetujuan Manager Operasional..." style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-color); color: #fff; font-size: 13px; resize: vertical; box-sizing: border-box;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeOverrideUnlockModal()" class="btn-action-sm" style="background: rgba(100, 116, 139, 0.2); border: 1px solid rgba(100, 116, 139, 0.4); color: #cbd5e1; cursor: pointer;">Batal</button>
                <button type="submit" class="btn-action-sm" style="background: rgba(245, 158, 11, 0.3); border: 1px solid rgba(245, 158, 11, 0.6); color: #fbbf24; font-weight: 700; cursor: pointer;">
                    🔓 Setujui Buka Kunci
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openOverrideUnlockModal(summaryId, kasirName, shiftName) {
    const modal = document.getElementById('modal-override-unlock');
    const form = document.getElementById('form-override-unlock');
    const empEl = document.getElementById('override-employee-name');
    const shiftEl = document.getElementById('override-shift-name');
    const reasonEl = document.getElementById('override-reason');

    if (form) {
        form.action = '{{ url("admin/penjualan/rekap-shift") }}/' + summaryId + '/unlock-sales';
    }
    if (empEl) empEl.innerText = kasirName;
    if (shiftEl) shiftEl.innerText = shiftName;
    if (reasonEl) reasonEl.value = '';

    if (modal) modal.style.display = 'flex';
}

function closeOverrideUnlockModal() {
    const modal = document.getElementById('modal-override-unlock');
    if (modal) modal.style.display = 'none';
}
function openDiscrepancyModal(summaryId, kasirName, diffAmount) {
    const modal = document.getElementById('modal-discrepancy-resolution');
    const form = document.getElementById('form-discrepancy-resolution');
    const kasirEl = document.getElementById('modal-disc-kasir');
    const nominalEl = document.getElementById('modal-disc-nominal');

    if (form) {
        form.action = '{{ url("admin/penjualan/rekap-shift") }}/' + summaryId + '/resolve-discrepancy';
    }
    if (kasirEl) kasirEl.innerText = kasirName;
    if (nominalEl) {
        const formatted = new Intl.NumberFormat('id-ID').format(Math.abs(diffAmount));
        if (diffAmount < 0) {
            nominalEl.innerHTML = `<span style="color: #f87171;">⚠️ Kurang -Rp ${formatted}</span>`;
        } else {
            nominalEl.innerHTML = `<span style="color: #38bdf8;">+Rp ${formatted} (Lebih)</span>`;
        }
    }

    if (modal) modal.style.display = 'flex';
}

function closeDiscrepancyModal() {
    const modal = document.getElementById('modal-discrepancy-resolution');
    if (modal) modal.style.display = 'none';
}
</script>
@endsection
