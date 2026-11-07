@extends('admin.dashboard.layout')

@section('title', 'Jurnal Umum (General Journal)')

@section('content')
<style>
    .coa-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(11, 17, 32, 0.85);
        backdrop-filter: blur(8px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .coa-modal-backdrop.open { display: flex; }
    .coa-modal-box {
        background: #1e293b;
        border: 1px solid rgba(56, 189, 248, 0.3);
        border-radius: 16px;
        width: 100%;
        max-width: 780px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
    }
    .coa-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .coa-modal-body { padding: 24px; }
    .journal-line-row {
        display: grid;
        grid-template-columns: 2.5fr 1.2fr 1.2fr 1.5fr 36px;
        gap: 8px;
        margin-bottom: 8px;
        align-items: center;
    }
</style>

<!-- HERO HEADER -->
<div class="hero-banner-card">
    <div>
        <h1 class="hero-title-text">Jurnal Umum (General Journal)</h1>
        <p class="hero-desc-text">
            Buku catatan kronologis transaksi keuangan berpasangan (*Double-Entry Bookkeeping*) yang otomatis terintegrasi dengan penjualan POS kasir dan kas operasional.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" onclick="openManualJournalModal()" class="btn-action-sm btn-action-emerald" style="padding: 10px 18px; font-size: 13px;">
            <span>➕</span> Catat Jurnal Manual
        </button>
        <a href="{{ route('admin.coa') }}" class="btn-action-sm btn-action-primary" style="padding: 10px 18px; font-size: 13px;">
            <span>🏛️</span> Bagan Akun (COA)
        </a>
    </div>
</div>

<!-- NAVIGATION PILLS -->
@include('admin.accounting.nav')

<!-- KPI RINGKASAN PERIODE -->
<div class="stats-grid-row">
    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Total Entri Jurnal</span>
            <div class="metric-stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">📖</div>
        </div>
        <div class="metric-stat-value">{{ number_format($entries->total(), 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Transaksi Tercatat</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Total Debit Periode</span>
            <div class="metric-stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">📥</div>
        </div>
        <div class="metric-stat-value">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Akun Bertambah/Masuk</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Total Kredit Periode</span>
            <div class="metric-stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">📤</div>
        </div>
        <div class="metric-stat-value">Rp {{ number_format($totalCredit, 0, ',', '.') }}</div>
        <div class="metric-stat-desc">Akun Berkurang/Keluar</div>
    </div>

    <div class="metric-stat-card">
        <div class="metric-stat-header">
            <span class="metric-stat-label">Status Keseimbangan</span>
            <div class="metric-stat-icon" style="background: {{ abs($totalDebit - $totalCredit) < 1 ? 'rgba(16, 185, 129, 0.15); color: #34d399;' : 'rgba(244, 63, 94, 0.15); color: #fb7185;' }}">⚖️</div>
        </div>
        <div class="metric-stat-value" style="{{ abs($totalDebit - $totalCredit) < 1 ? 'color: #34d399;' : 'color: #fb7185;' }}">
            {{ abs($totalDebit - $totalCredit) < 1 ? 'SEIMBANG' : 'SELISIH' }}
        </div>
        <div class="metric-stat-desc">{{ abs($totalDebit - $totalCredit) < 1 ? 'Debit == Kredit (Sempurna)' : 'Selisih Rp '.number_format(abs($totalDebit - $totalCredit), 0) }}</div>
    </div>
</div>

<!-- FILTER CONTROLS -->
<div class="dashboard-section-card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.akuntansi.jurnal') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 12px; color: #94a3b8;">Dari:</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-input-custom" style="padding: 6px 12px; width: 140px;">
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 12px; color: #94a3b8;">Sampai:</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-input-custom" style="padding: 6px 12px; width: 140px;">
        </div>
        <div style="flex: 1; min-width: 180px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari No. Jurnal / Keterangan..." class="form-input-custom" style="padding: 6px 12px; width: 100%;">
        </div>
        <button type="submit" class="btn-action-sm btn-action-primary" style="padding: 8px 16px;">
            <span>🔍</span> Terapkan Filter
        </button>
        @if(request()->anyFilled(['start_date', 'end_date', 'q', 'status']))
        <a href="{{ route('admin.akuntansi.jurnal') }}" class="btn-action-sm" style="padding: 8px 14px; background: rgba(255,255,255,0.05); color: #94a3b8;">
            Reset
        </a>
        @endif
    </form>
</div>

<!-- TABEL JURNAL UMUM -->
<div class="dashboard-section-card">
    <div class="section-card-header">
        <div class="section-header-left">
            <div class="section-header-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">📖</div>
            <div>
                <h3 class="section-title">Daftar Jurnal Pembukuan</h3>
                <span class="section-subtitle">Periode {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-wrapper">
        <table class="custom-data-table">
            <thead>
                <tr>
                    <th style="width: 130px;">Tanggal & No. Jurnal</th>
                    <th style="width: 250px;">Keterangan / Referensi</th>
                    <th>Akun Buku Besar & Rincian Transaksi</th>
                    <th style="width: 140px; text-align: right;">Debit (Rp)</th>
                    <th style="width: 140px; text-align: right;">Kredit (Rp)</th>
                    <th style="width: 90px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                <tr style="border-top: 1px solid rgba(255,255,255,0.12); background: rgba(15, 23, 42, 0.4);">
                    <td style="vertical-align: top;">
                        <strong style="color: #fff; font-size: 13px;">{{ $entry->entry_date->format('d/m/Y') }}</strong>
                        <div style="font-family: monospace; font-size: 11px; color: #38bdf8; margin-top: 2px;">{{ $entry->entry_number }}</div>
                    </td>
                    <td style="vertical-align: top;">
                        <div style="font-weight: 600; color: #e2e8f0; font-size: 13px;">{{ $entry->description }}</div>
                        @if($entry->branch)
                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Cabang: {{ $entry->branch->name }}</div>
                        @endif
                    </td>
                    <td colspan="3" style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            @foreach($entry->lines as $line)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <td style="padding: 8px 12px; font-size: 12px;">
                                    <div style="{{ $line->credit > 0 ? 'padding-left: 20px;' : '' }}">
                                        <span style="font-family: monospace; color: #94a3b8; font-weight: 600;">{{ $line->chartOfAccount->account_code ?? '-' }}</span>
                                        <strong style="color: {{ $line->debit > 0 ? '#38bdf8' : '#e2e8f0' }}; margin-left: 6px;">{{ $line->chartOfAccount->account_name ?? 'Akun Terhapus' }}</strong>
                                        @if($line->memo)
                                        <span style="font-size: 11px; color: #64748b; margin-left: 6px;">({{ $line->memo }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="width: 140px; text-align: right; padding: 8px 12px; font-size: 12px; font-family: monospace; color: {{ $line->debit > 0 ? '#34d399' : '#64748b' }};">
                                    {{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td style="width: 140px; text-align: right; padding: 8px 12px; font-size: 12px; font-family: monospace; color: {{ $line->credit > 0 ? '#fbbf24' : '#64748b' }};">
                                    {{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                    <td style="vertical-align: top; text-align: center; padding-top: 12px;">
                        <span class="badge-status {{ $entry->status === 'posted' ? 'badge-emerald' : ($entry->status === 'void' ? 'badge-rose' : 'badge-amber') }}" style="font-size: 10px;">
                            {{ strtoupper($entry->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                        Tidak ada entri jurnal dalam rentang tanggal ini. Silakan sinkronkan transaksi atau input jurnal manual.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($entries->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.08);">
        {{ $entries->links() }}
    </div>
    @endif
</div>

<!-- MODAL INPUT JURNAL MANUAL -->
<div id="modalManualJournal" class="coa-modal-backdrop">
    <div class="coa-modal-box">
        <form method="POST" action="{{ route('admin.akuntansi.jurnal.manual') }}" onsubmit="return validateManualJournal()">
            @csrf
            <div class="coa-modal-header">
                <div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; color: #fff; font-weight: 700;">
                        <span>✍️</span> Input Jurnal Umum Manual
                    </h3>
                    <p style="font-size: 12px; color: #94a3b8;">Catat penyesuaian beban, setoran modal, atau pemindahan saldo antar-akun.</p>
                </div>
                <button type="button" onclick="closeManualJournalModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">✕</button>
            </div>

            <div class="coa-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label class="form-label-custom">Tanggal Entri *</label>
                        <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required class="form-input-custom">
                    </div>
                    <div>
                        <label class="form-label-custom">Cabang / Outlet</label>
                        <select name="branch_id" class="form-input-custom">
                            <option value="">Semua Cabang / Kantor Pusat</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="form-label-custom">Keterangan Jurnal *</label>
                    <input type="text" name="description" placeholder="Contoh: Pembayaran Beban Listrik PLN Shift 1" required class="form-input-custom">
                </div>

                <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: #fff; font-size: 13px;">Rincian Akun (Debit & Kredit Wajib Seimbang)</strong>
                    <button type="button" onclick="addJournalLine()" class="btn-action-sm btn-action-primary" style="padding: 4px 10px; font-size: 11px;">
                        + Tambah Baris
                    </button>
                </div>

                <div id="journalLinesContainer">
                    <!-- LINE 1 (DEBIT) -->
                    <div class="journal-line-row">
                        <select name="lines[0][chart_of_account_id]" required class="form-input-custom" style="padding: 6px 10px; font-size: 12px;">
                            <option value="">-- Pilih Akun Debet --</option>
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="any" min="0" name="lines[0][debit]" placeholder="Debit (Rp)" class="form-input-custom line-debit" oninput="calcJournalBalance()" style="padding: 6px 10px; font-size: 12px; text-align: right;">
                        <input type="number" step="any" min="0" name="lines[0][credit]" placeholder="Kredit (Rp)" class="form-input-custom line-credit" oninput="calcJournalBalance()" style="padding: 6px 10px; font-size: 12px; text-align: right;" value="0">
                        <input type="text" name="lines[0][memo]" placeholder="Memo baris..." class="form-input-custom" style="padding: 6px 10px; font-size: 12px;">
                        <button type="button" onclick="removeJournalLine(this)" style="background: none; border: none; color: #fb7185; cursor: pointer; font-size: 16px;">✕</button>
                    </div>

                    <!-- LINE 2 (KREDIT) -->
                    <div class="journal-line-row">
                        <select name="lines[1][chart_of_account_id]" required class="form-input-custom" style="padding: 6px 10px; font-size: 12px;">
                            <option value="">-- Pilih Akun Kredit --</option>
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="any" min="0" name="lines[1][debit]" placeholder="Debit (Rp)" class="form-input-custom line-debit" oninput="calcJournalBalance()" style="padding: 6px 10px; font-size: 12px; text-align: right;" value="0">
                        <input type="number" step="any" min="0" name="lines[1][credit]" placeholder="Kredit (Rp)" class="form-input-custom line-credit" oninput="calcJournalBalance()" style="padding: 6px 10px; font-size: 12px; text-align: right;">
                        <input type="text" name="lines[1][memo]" placeholder="Memo baris..." class="form-input-custom" style="padding: 6px 10px; font-size: 12px;">
                        <button type="button" onclick="removeJournalLine(this)" style="background: none; border: none; color: #fb7185; cursor: pointer; font-size: 16px;">✕</button>
                    </div>
                </div>

                <!-- BALANCE COUNTER -->
                <div style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 12px 16px; margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 12px; color: #94a3b8;">Total Debit: <strong id="sumDebit" style="color: #34d399;">Rp 0</strong></span>
                        <span style="margin: 0 10px; color: #64748b;">|</span>
                        <span style="font-size: 12px; color: #94a3b8;">Total Kredit: <strong id="sumCredit" style="color: #fbbf24;">Rp 0</strong></span>
                    </div>
                    <div id="balanceStatusText" style="font-size: 12px; font-weight: 700; color: #34d399;">
                        ⚖️ SEIMBANG (Rp 0)
                    </div>
                </div>
            </div>

            <div style="padding: 16px 24px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeManualJournalModal()" class="btn-action-sm" style="background: rgba(255,255,255,0.06); color: #94a3b8; padding: 10px 16px;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitJournal" class="btn-action-sm btn-action-emerald" style="padding: 10px 20px;">
                    <span>💾</span> Simpan & Posting Jurnal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let lineIndex = 2;
const accountOptionsHtml = `{!! addslashes($accounts->map(fn($a) => "<option value='{$a->id}'>{$a->account_code} - {$a->account_name}</option>")->implode('')) !!}`;

function openManualJournalModal() {
    document.getElementById('modalManualJournal').classList.add('open');
    calcJournalBalance();
}

function closeManualJournalModal() {
    document.getElementById('modalManualJournal').classList.remove('open');
}

function addJournalLine() {
    const container = document.getElementById('journalLinesContainer');
    const div = document.createElement('div');
    div.className = 'journal-line-row';
    div.innerHTML = `
        <select name="lines[${lineIndex}][chart_of_account_id]" required class="form-input-custom" style="padding: 6px 10px; font-size: 12px;">
            <option value="">-- Pilih Akun --</option>
            ${accountOptionsHtml}
        </select>
        <input type="number" step="any" min="0" name="lines[${lineIndex}][debit]" placeholder="Debit (Rp)" class="form-input-custom line-debit" oninput="calcJournalBalance()" style="padding: 6px 10px; font-size: 12px; text-align: right;" value="0">
        <input type="number" step="any" min="0" name="lines[${lineIndex}][credit]" placeholder="Kredit (Rp)" class="form-input-custom line-credit" oninput="calcJournalBalance()" style="padding: 6px 10px; font-size: 12px; text-align: right;" value="0">
        <input type="text" name="lines[${lineIndex}][memo]" placeholder="Memo baris..." class="form-input-custom" style="padding: 6px 10px; font-size: 12px;">
        <button type="button" onclick="removeJournalLine(this)" style="background: none; border: none; color: #fb7185; cursor: pointer; font-size: 16px;">✕</button>
    `;
    container.appendChild(div);
    lineIndex++;
    calcJournalBalance();
}

function removeJournalLine(btn) {
    const rows = document.querySelectorAll('.journal-line-row');
    if (rows.length <= 2) {
        alert('Jurnal minimal memiliki 2 baris (Debit dan Kredit).');
        return;
    }
    btn.closest('.journal-line-row').remove();
    calcJournalBalance();
}

function calcJournalBalance() {
    let debits = 0;
    let credits = 0;
    document.querySelectorAll('.line-debit').forEach(inp => { debits += parseFloat(inp.value) || 0; });
    document.querySelectorAll('.line-credit').forEach(inp => { credits += parseFloat(inp.value) || 0; });

    document.getElementById('sumDebit').innerText = 'Rp ' + Math.round(debits).toLocaleString('id-ID');
    document.getElementById('sumCredit').innerText = 'Rp ' + Math.round(credits).toLocaleString('id-ID');

    const diff = Math.abs(debits - credits);
    const statusText = document.getElementById('balanceStatusText');
    const submitBtn = document.getElementById('btnSubmitJournal');

    if (diff < 0.01 && debits > 0) {
        statusText.style.color = '#34d399';
        statusText.innerHTML = '⚖️ SEIMBANG (Rp 0)';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
    } else {
        statusText.style.color = '#fb7185';
        statusText.innerHTML = '⚠️ SELISIH: Rp ' + Math.round(diff).toLocaleString('id-ID');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
    }
}

function validateManualJournal() {
    let debits = 0;
    let credits = 0;
    document.querySelectorAll('.line-debit').forEach(inp => { debits += parseFloat(inp.value) || 0; });
    document.querySelectorAll('.line-credit').forEach(inp => { credits += parseFloat(inp.value) || 0; });

    if (Math.abs(debits - credits) >= 0.01) {
        alert('Total Debit dan Total Kredit harus seimbang!');
        return false;
    }
    if (debits <= 0) {
        alert('Nominal jurnal tidak boleh 0!');
        return false;
    }
    return true;
}
</script>
@endsection

