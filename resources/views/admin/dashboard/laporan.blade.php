@extends('admin.dashboard.layout')

@section('title', '4. Laporan Rekapitulasi & Keuangan')

@section('content')
<!-- SECTION HEADER -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(99, 102, 241, 0.15); color: #818cf8;">📊</div>
            <div>
                <h1 class="section-title" style="font-size: 20px;">4. Laporan Rekapitulasi & Keuangan</h1>
                <span class="section-subtitle">Ringkasan Omset Penjualan F&B, Pendapatan Billing Internet, & Serah Terima Kasir</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button onclick="window.print()" class="btn-action-sm btn-action-primary" style="padding: 8px 14px;">
                <span>🖨️</span> Cetak / Simpan PDF
            </button>
            <a href="{{ route('admin.index') }}" class="btn-action-sm" style="background: rgba(255,255,255,0.08); color: #fff;">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- STAT SUMMARY ROW -->
    <div style="padding: 20px 24px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
            <div style="background: rgba(15, 23, 42, 0.6); padding: 18px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Omset Penjualan F&B</span>
                <div style="font-size: 24px; font-weight: 800; color: #fbbf24; font-family: 'Outfit'; margin: 4px 0;">
                    Rp {{ number_format($reportSummary['total_fnb_sales_today'], 0, ',', '.') }}
                </div>
                <span style="font-size: 11px; color: var(--text-sub);">Makanan, snack & minuman showcase</span>
            </div>

            <div style="background: rgba(15, 23, 42, 0.6); padding: 18px; border-radius: 12px; border: 1px solid var(--border-glass);">
                <span style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Pendapatan Billing Warnet</span>
                <div style="font-size: 24px; font-weight: 800; color: #38bdf8; font-family: 'Outfit'; margin: 4px 0;">
                    Rp {{ number_format($reportSummary['total_billing_sales_today'] ?: $billingPerShift['total_daily_billing'], 0, ',', '.') }}
                </div>
                <span style="font-size: 11px; color: var(--text-sub);">Sewa PC Station 1-15 & Member</span>
            </div>

            <div style="background: rgba(16, 185, 129, 0.08); padding: 18px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                <span style="font-size: 11px; text-transform: uppercase; color: #34d399; font-weight: 700;">Total Omset Keseluruhan</span>
                <div style="font-size: 26px; font-weight: 800; color: #34d399; font-family: 'Outfit'; margin: 4px 0;">
                    Rp {{ number_format($reportSummary['total_revenue_today'] ?: ($reportSummary['total_fnb_sales_today'] + $billingPerShift['total_daily_billing']), 0, ',', '.') }}
                </div>
                <span style="font-size: 11px; color: var(--text-muted);">{{ $reportSummary['total_transactions_today'] }} Transaksi Terdata Hari Ini</span>
            </div>
        </div>
    </div>

    <div class="section-card-body">
        <!-- AUDIT KAS DI LACI -->
        <div style="background: rgba(15, 23, 42, 0.4); border: 1px solid var(--border-glass); border-radius: 12px; padding: 16px 20px; font-size: 13px; color: var(--text-muted); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
            <div>
                <strong>Audit Serah Terima Kasir:</strong> Tercatat <strong>{{ $reportSummary['shift_handover_count'] }} sesi pergantian shift</strong> dengan total uang fisik laci kasir sebesar <strong style="color: #34d399; font-family: 'Outfit'; font-size: 15px;">Rp {{ number_format($reportSummary['total_drawer_cash'], 0, ',', '.') }}</strong>.
            </div>
            <a href="{{ route('admin.penjualan') }}" class="btn-action-sm btn-action-primary">
                <span>🔍</span> Buka Buku Penjualan Lengkap
            </a>
        </div>

        <!-- TABEL RINCIAN PENDAPATAN SHIFT OPERASIONAL -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                    <span>💵</span> Rekap Pendapatan per Shift Operasional ({{ $billingPerShift['date_formatted'] }})
                </h3>
            </div>

            <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 12px;">
                <table class="custom-data-table">
                    <thead>
                        <tr>
                            <th>Shift</th>
                            <th>Jam Operasional</th>
                            <th>Operator Bertugas</th>
                            <th>Billing Internet</th>
                            <th>Penjualan F&B Kasir</th>
                            <th>Total Omset Shift</th>
                            <th>Status Serah Terima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billingPerShift['shifts'] as $bps)
                        <tr>
                            <td style="font-weight: 700; color: #38bdf8;">{{ $bps['shift_name'] }}</td>
                            <td style="font-family: monospace;">{{ $bps['time_range'] }}</td>
                            <td style="color: #fff; font-weight: 600;">{{ $bps['operator_name'] }}</td>
                            <td style="color: #38bdf8; font-weight: 700; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['billing_amount'], 0, ',', '.') }}
                            </td>
                            <td style="color: #fbbf24; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['fnb_amount'], 0, ',', '.') }}
                            </td>
                            <td style="color: #34d399; font-weight: 800; font-family: 'Outfit', monospace;">
                                Rp {{ number_format($bps['total_amount'], 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge-status badge-sky">{{ $bps['status'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
