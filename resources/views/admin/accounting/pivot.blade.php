@extends('admin.dashboard.layout')

@section('title', 'Pivot Analisis Transaksi SAK')

@section('content')
<style>
    .pivot-matrix-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }
    .pivot-matrix-table th, .pivot-matrix-table td {
        padding: 10px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        border-right: 1px solid rgba(255, 255, 255, 0.05);
    }
    .pivot-matrix-table thead th {
        background: #0f172a;
        color: #94a3b8;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .pivot-matrix-table tbody tr:hover td {
        background: rgba(56, 189, 248, 0.04);
    }
    .pivot-row-header {
        font-weight: 600;
        color: #f1f5f9;
        background: rgba(15, 23, 42, 0.4);
        min-width: 260px;
    }
    .pivot-cell-val {
        text-align: right;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        white-space: nowrap;
    }
    .pivot-total-col {
        background: rgba(30, 41, 59, 0.7);
        font-weight: 700;
        color: #38bdf8;
    }
    .pivot-grand-row td {
        background: #0f172a !important;
        font-weight: 800;
        border-top: 2px solid rgba(56, 189, 248, 0.4);
        border-bottom: 2px solid rgba(56, 189, 248, 0.4);
    }
    @media print {
        .hero-banner-card, .tab-nav-pills, .filter-box-card, .btn-no-print {
            display: none !important;
        }
        .dashboard-section-card {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            color: #000 !important;
        }
        .pivot-matrix-table th, .pivot-matrix-table td {
            color: #000 !important;
            border-color: #ccc !important;
        }
    }
</style>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Pivot Analisis Transaksi SAK</h1>
        <p class="hero-desc-text">
            Tabel matriks pivot multidimensi data jurnal umum akuntansi standar SAK. Lakukan analisis silang antara akun COA, unit cabang, periode waktu, dan kategori transaksi.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="btn-no-print">
        <a href="{{ request()->fullUrlWithQuery(['format' => 'csv']) }}" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px;">
            <span>📥</span> Unduh Excel / CSV
        </a>
        <button type="button" onclick="window.print()" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>🖨️</span> Cetak Laporan
        </button>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- FILTER & PIVOT CONFIGURATION BAR -->
<div class="dashboard-section-card filter-box-card" style="padding: 20px; margin-bottom: 24px;">
    <form method="GET" action="{{ route('admin.akuntansi.pivot') }}" id="pivotFilterForm">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <!-- Rentang Tanggal -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Dari Tanggal:</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
            </div>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Sampai Tanggal:</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
            </div>

            <!-- Filter Cabang -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Cabang / Outlet:</label>
                <select name="branch_id" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                    <option value="all" {{ $selectedBranchId === 'all' ? 'selected' : '' }}>🏢 Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Kategori SAK -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 6px;">Kategori Akun SAK:</label>
                <select name="category" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                    <option value="all" {{ $selectedCategory === 'all' ? 'selected' : '' }}>📁 Semua Kategori (1-6)</option>
                    @foreach($categoryLabels as $catKey => $catName)
                        <option value="{{ $catKey }}" {{ $selectedCategory === $catKey ? 'selected' : '' }}>
                            {{ $catName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
            <!-- Dimensi Baris -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #38bdf8; margin-bottom: 6px;">
                    <span>↕️</span> Dimensi Baris (Rows):
                </label>
                <select name="row_dim" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                    <option value="account" {{ $rowDim === 'account' ? 'selected' : '' }}>Akun COA (Bagan Akun)</option>
                    <option value="category" {{ $rowDim === 'category' ? 'selected' : '' }}>Kategori Akun SAK</option>
                    <option value="branch" {{ $rowDim === 'branch' ? 'selected' : '' }}>Cabang / Lokasi</option>
                    <option value="source_type" {{ $rowDim === 'source_type' ? 'selected' : '' }}>Tipe Sumber Transaksi</option>
                </select>
            </div>

            <!-- Dimensi Kolom -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #38bdf8; margin-bottom: 6px;">
                    <span>↔️</span> Dimensi Kolom (Columns):
                </label>
                <select name="col_dim" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                    <option value="branch" {{ $colDim === 'branch' ? 'selected' : '' }}>Cabang / Lokasi</option>
                    <option value="month" {{ $colDim === 'month' ? 'selected' : '' }}>Bulan Transaksi (Periode)</option>
                    <option value="category" {{ $colDim === 'category' ? 'selected' : '' }}>Kategori Akun SAK</option>
                    <option value="source_type" {{ $colDim === 'source_type' ? 'selected' : '' }}>Tipe Sumber Transaksi</option>
                </select>
            </div>

            <!-- Metrik Sel Matrix -->
            <div>
                <label style="display: block; font-size: 12px; font-weight: 600; color: #38bdf8; margin-bottom: 6px;">
                    <span>🔢</span> Nilai Metrik Ditampilkan:
                </label>
                <select name="metric" class="form-input-custom" style="width: 100%; padding: 8px 12px;">
                    <option value="net" {{ $metric === 'net' ? 'selected' : '' }}>Saldo Bersih Mutasi SAK</option>
                    <option value="debit" {{ $metric === 'debit' ? 'selected' : '' }}>Hanya Total Debit (Rp)</option>
                    <option value="credit" {{ $metric === 'credit' ? 'selected' : '' }}>Hanya Total Kredit (Rp)</option>
                    <option value="both" {{ $metric === 'both' ? 'selected' : '' }}>Debit & Kredit (Berdampingan)</option>
                </select>
            </div>

            <!-- Tombol Submit -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 9px 20px; flex: 1;">
                    <span>⚡</span> Terapkan Pivot
                </button>
                <a href="{{ route('admin.akuntansi.pivot') }}" class="btn-action-sm" style="padding: 9px 14px; background: rgba(255, 255, 255, 0.1); color: #cbd5e1;">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- STAT CARDS RINGKASAN PIVOT -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Debit Terproses</div>
        <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #38bdf8; margin-top: 6px;">
            Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Arus masuk debit akun SAK</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Total Kredit Terproses</div>
        <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #34d399; margin-top: 6px;">
            Rp {{ number_format($grandTotalCredit, 0, ',', '.') }}
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Arus keluar kredit akun SAK</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Saldo Mutasi Bersih SAK</div>
        <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #fbbf24; margin-top: 6px;">
            Rp {{ number_format($grandTotalNet, 0, ',', '.') }}
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Net movement sesuai normal balance</div>
    </div>

    <div class="dashboard-stat-card" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(168, 85, 247, 0.2); border-radius: 14px; padding: 18px 20px;">
        <div style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase;">Frekuensi Entri Jurnal</div>
        <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #c084fc; margin-top: 6px;">
            {{ number_format($grandTotalCount, 0, ',', '.') }} Baris
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Jurnal status diposting (*posted*)</div>
    </div>
</div>

<!-- TABEL MATRIKS PIVOT -->
<div class="dashboard-section-card" style="overflow: hidden;">
    <div class="section-card-header" style="padding: 18px 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div class="section-header-left" style="display: flex; align-items: center; gap: 12px;">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 10px; border-radius: 10px; font-size: 18px;">📊</div>
            <div>
                <h3 class="section-title" style="font-size: 16px; font-weight: 700; color: #fff; margin: 0;">
                    Matriks Pivot Transaksi: {{ ucwords(str_replace('_', ' ', $rowDim)) }} × {{ ucwords(str_replace('_', ' ', $colDim)) }}
                </h3>
                <span class="section-subtitle" style="font-size: 12px; color: #94a3b8;">
                    Periode {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}
                </span>
            </div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #34d399;"></span>
            Prinsip SAK Dipatuhi
        </div>
    </div>

    @if(empty($rows) || empty($columns))
        <div style="padding: 60px 20px; text-align: center; color: #94a3b8;">
            <div style="font-size: 40px; margin-bottom: 12px;">📂</div>
            <h4 style="font-size: 16px; color: #f1f5f9; margin-bottom: 6px;">Tidak Ada Data Transaksi untuk Rentang Ini</h4>
            <p style="font-size: 13px; max-width: 480px; margin: 0 auto;">
                Silakan ubah rentang tanggal, filter cabang, atau pastikan jurnal umum pada periode tersebut telah berstatus diposting (*posted*).
            </p>
        </div>
    @else
        <div class="table-responsive-wrapper" style="overflow-x: auto; max-height: 650px;">
            <table class="pivot-matrix-table">
                <thead>
                    <tr>
                        <th class="pivot-row-header" style="z-index: 20;">
                            {{ ucwords(str_replace('_', ' ', $rowDim)) }}
                        </th>
                        @foreach($columns as $col)
                            <th style="text-align: right; min-width: 140px;">
                                {{ $col['label'] }}
                            </th>
                        @endforeach
                        <th class="pivot-total-col" style="text-align: right; min-width: 150px;">Total Debit</th>
                        <th class="pivot-total-col" style="text-align: right; min-width: 150px;">Total Kredit</th>
                        <th class="pivot-total-col" style="text-align: right; min-width: 160px; color: #34d399;">Mutasi Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $rKey => $row)
                        <tr>
                            <td class="pivot-row-header">
                                <span style="font-weight: 600;">{{ $row['label'] }}</span>
                            </td>

                            @foreach($columns as $cKey => $col)
                                @php
                                    $cell = $matrix[$rKey][$cKey] ?? null;
                                    $debitVal = (float)($cell['debit'] ?? 0);
                                    $creditVal = (float)($cell['credit'] ?? 0);
                                    $netVal = (float)($cell['net'] ?? 0);
                                    $countVal = (int)($cell['count'] ?? 0);
                                @endphp
                                <td class="pivot-cell-val">
                                    @if(!$cell || ($debitVal == 0 && $creditVal == 0))
                                        <span style="color: #475569;">—</span>
                                    @else
                                        @if($metric === 'net')
                                            <div style="font-weight: 600; color: {{ $netVal >= 0 ? '#f1f5f9' : '#fb7185' }};">
                                                Rp {{ number_format($netVal, 0, ',', '.') }}
                                            </div>
                                            <div style="font-size: 10px; color: #64748b;">{{ $countVal }} entri</div>
                                        @elseif($metric === 'debit')
                                            <div style="color: #38bdf8; font-weight: 600;">
                                                Rp {{ number_format($debitVal, 0, ',', '.') }}
                                            </div>
                                        @elseif($metric === 'credit')
                                            <div style="color: #34d399; font-weight: 600;">
                                                Rp {{ number_format($creditVal, 0, ',', '.') }}
                                            </div>
                                        @else
                                            <div style="color: #38bdf8; font-size: 11px;">D: {{ number_format($debitVal, 0, ',', '.') }}</div>
                                            <div style="color: #34d399; font-size: 11px;">K: {{ number_format($creditVal, 0, ',', '.') }}</div>
                                        @endif
                                    @endif
                                </td>
                            @endforeach

                            <!-- Row Totals -->
                            <td class="pivot-cell-val pivot-total-col" style="color: #38bdf8;">
                                Rp {{ number_format($row['total_debit'], 0, ',', '.') }}
                            </td>
                            <td class="pivot-cell-val pivot-total-col" style="color: #34d399;">
                                Rp {{ number_format($row['total_credit'], 0, ',', '.') }}
                            </td>
                            <td class="pivot-cell-val pivot-total-col" style="color: {{ $row['total_net'] >= 0 ? '#34d399' : '#fb7185' }}; font-weight: 800;">
                                Rp {{ number_format($row['total_net'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="pivot-grand-row">
                        <td class="pivot-row-header" style="font-weight: 800; color: #38bdf8;">
                            GRAND TOTAL
                        </td>
                        @foreach($columns as $col)
                            @php
                                $colNet = (float)$col['total_net'];
                                $colDebit = (float)$col['total_debit'];
                                $colCredit = (float)$col['total_credit'];
                            @endphp
                            <td class="pivot-cell-val" style="font-weight: 800;">
                                @if($metric === 'net')
                                    <div style="color: #38bdf8;">
                                        Rp {{ number_format($colNet, 0, ',', '.') }}
                                    </div>
                                @elseif($metric === 'debit')
                                    <div style="color: #38bdf8;">
                                        Rp {{ number_format($colDebit, 0, ',', '.') }}
                                    </div>
                                @elseif($metric === 'credit')
                                    <div style="color: #34d399;">
                                        Rp {{ number_format($colCredit, 0, ',', '.') }}
                                    </div>
                                @else
                                    <div style="color: #38bdf8; font-size: 11px;">D: {{ number_format($colDebit, 0, ',', '.') }}</div>
                                    <div style="color: #34d399; font-size: 11px;">K: {{ number_format($colCredit, 0, ',', '.') }}</div>
                                @endif
                            </td>
                        @endforeach
                        <td class="pivot-cell-val" style="color: #38bdf8; font-weight: 800;">
                            Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
                        </td>
                        <td class="pivot-cell-val" style="color: #34d399; font-weight: 800;">
                            Rp {{ number_format($grandTotalCredit, 0, ',', '.') }}
                        </td>
                        <td class="pivot-cell-val" style="color: #fbbf24; font-weight: 800;">
                            Rp {{ number_format($grandTotalNet, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection
