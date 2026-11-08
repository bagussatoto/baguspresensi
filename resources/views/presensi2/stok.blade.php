@extends('layouts.presensi2')

@section('title', 'Pemeriksaan Stok Barang & Serah Terima Shift')

@section('content')
<div class="stok-page">
    @if(session('status') || session('success'))
    <div class="glass-card" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); padding: 12px 18px; margin-bottom: 14px; border-radius: 12px; color: #34d399; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>✓</span> {{ session('status') ?? session('success') }}
    </div>
    @endif

    {{-- HEADER KARYAWAN & SHIFT AKTIF --}}
    <div class="glass-card" style="border-color: rgba(139, 92, 246, 0.25); padding: 18px 20px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #7c3aed, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4);">
                    📦
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">Serah Terima Shift Operasional</span>
                    <h2 style="font-size: 17px; font-weight: 700; color: #fff; margin-top: 2px;">
                        Pemeriksaan Stok & Aset Cabang
                    </h2>
                </div>
            </div>
            <div style="text-align: right;">
                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 12px; padding: 4px 12px;">
                    📍 {{ $currentEmployee->branch->name ?? 'MGK Network' }}
                </span>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                    Operator: <strong style="color: #c4b5fd;">{{ $currentEmployee->name }}</strong>
                </div>
            </div>
        </div>

        @if($activeShift)
        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-glass); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; font-size: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="color: var(--text-sub);">Shift Dinas:</span>
                <span class="badge" style="background: rgba(139, 92, 246, 0.2); color: #c4b5fd; font-weight: 700;">
                    {{ $activeShift->code }}
                </span>
                <strong style="color: #e5e7eb;">{{ $activeShift->name }} ({{ substr($activeShift->start_time, 0, 5) }} - {{ substr($activeShift->end_time, 0, 5) }})</strong>
            </div>
            <div style="color: var(--accent-cyan); font-weight: 600;">
                📅 {{ \Carbon\Carbon::parse($workDate)->translatedFormat('l, d F Y') }}
            </div>
        </div>
        @endif
    </div>

    {{-- PANDUAN RINGKAS / BANNER STATUS VERIFIKASI STOK SHIFT AKTIF --}}
    @if($existingChecklist && $existingChecklist->status === 'completed')
    <div class="glass-card" style="background: rgba(16, 185, 129, 0.12); border-color: rgba(16, 185, 129, 0.4); padding: 16px 20px; margin-bottom: 18px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; gap: 12px; align-items: center;">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.25); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    ✅
                </div>
                <div>
                    <div style="font-size: 14px; font-weight: 800; color: #34d399;">
                        Stok Cabang Hari Ini Sudah Diverifikasi ({{ $existingChecklist->shift->name ?? $activeShift->name ?? 'Shift Ini' }})
                    </div>
                    <div style="font-size: 11.5px; color: #d1fae5; margin-top: 2px;">
                        Diverifikasi pukul <strong>{{ $existingChecklist->completed_at?->format('H:i') ?? $existingChecklist->updated_at?->format('H:i') }}</strong>
                        oleh <strong>{{ $existingChecklist->employee->name ?? $currentEmployee->name }}</strong>. Anda dapat langsung masuk ke Kasir Penjualan atau perbarui form di bawah jika ada perubahan fisik stok.
                    </div>
                </div>
            </div>
            <a href="{{ route('presensi2.penjualan') }}" class="btn btn-emerald" style="padding: 10px 18px; font-size: 13px; font-weight: 700; width: auto; text-decoration: none; white-space: nowrap; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                <span>🛒</span> Langsung ke Kasir Penjualan →
            </a>
        </div>
    </div>
    @else
    <div class="glass-card" style="background: rgba(14, 165, 233, 0.08); border-color: rgba(14, 165, 233, 0.25); padding: 14px 18px; margin-bottom: 18px;">
        <div style="display: flex; gap: 12px; align-items: flex-start;">
            <span style="font-size: 20px; line-height: 1;">📝</span>
            <div style="font-size: 12px; color: #bae6fd; line-height: 1.5;">
                <strong>Instruksi Pergantian Shift ({{ $activeShift->name ?? 'Shift Aktif' }}):</strong> Setiap operator shift (Shift 1 s/d 4) <strong>wajib memeriksa ulang fisik stok</strong> pada saat pergantian shift guna mendeteksi kekurangan barang. Pastikan ketersediaan <strong>kertas (A4 & F4)</strong>, stok <strong>minuman & makanan</strong>, kelayakan <strong>hardware</strong>, serta serah terima kas sebelum masuk ke Kasir Penjualan.
            </div>
        </div>
    </div>
    @endif

    {{-- BILAH MODE SIMULASI & PENGUJIAN STOK CEPAT --}}
    <div class="glass-card" style="background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.35); padding: 12px 18px; margin-bottom: 16px; border-radius: 12px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 16px;">🧪</span>
                <div>
                    <span style="font-size: 12px; font-weight: 700; color: #c084fc;">Mode Simulasi & Pengujian Serah Terima:</span>
                    <span style="font-size: 11px; color: var(--text-muted); display: block;">Uji skenario selisih stok secara instan tanpa perlu input manual satu per satu</span>
                </div>
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                {{-- PRESET 1: SEMUA COCOK --}}
                <button type="button" onclick="applyStockPreset('match')" class="btn" style="padding: 5px 11px; font-size: 11px; font-weight: 700; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.5); color: #34d399; border-radius: 6px; cursor: pointer;">
                    🟢 1. Preset Semua Cocok (Nihil)
                </button>
                {{-- PRESET 2: SIMULASI SELISIH KURANG (AQUA -1) --}}
                <button type="button" onclick="applyStockPreset('shortage')" class="btn" style="padding: 5px 11px; font-size: 11px; font-weight: 700; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; border-radius: 6px; cursor: pointer;">
                    🔴 2. Preset Selisih Kurang (Aqua -1)
                </button>
                {{-- PRESET 3: SIMULASI STOK MENIPIS --}}
                <button type="button" onclick="applyStockPreset('critical')" class="btn" style="padding: 5px 11px; font-size: 11px; font-weight: 700; background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.5); color: #fbbf24; border-radius: 6px; cursor: pointer;">
                    🟡 3. Preset Kritis (Restock)
                </button>
                {{-- RESET SIMULASI --}}
                <a href="{{ request()->fullUrlWithQuery(['action' => 'reset_stok']) }}" 
                   onclick="return confirm('Reset checklist simulasi hari ini? Data serah terima shift testing akan dibersihkan agar form kembali kosong.')" 
                   class="btn" 
                   style="padding: 5px 11px; font-size: 11px; font-weight: 700; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #cbd5e1; border-radius: 6px; text-decoration: none;">
                    🔄 Reset Simulasi
                </a>
            </div>
        </div>
    </div>

    {{-- KARTU NOTIFIKASI & PENERIMAAN TRANSFER DARI DC (STORE GOODS RECEIPT) --}}
    @if(!empty($pendingStoreTransfers) && $pendingStoreTransfers->count() > 0)
    <div class="glass-card" style="background: rgba(2, 132, 199, 0.12); border: 1.5px solid rgba(56, 189, 248, 0.5); padding: 18px 20px; margin-bottom: 20px; border-radius: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid rgba(56, 189, 248, 0.25); padding-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">🚚</span>
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; color: #38bdf8; margin: 0;">
                        Ada {{ $pendingStoreTransfers->count() }} Kiriman Pasokan Transfer dari DC Tiba (In-Transit)
                    </h3>
                    <div style="font-size: 11.5px; color: #bae6fd; margin-top: 2px;">
                        Verifikasi kuantitas fisik kiriman dari Distribution Center (DC) untuk langsung dimasukkan ke inventaris rak toko Anda.
                    </div>
                </div>
            </div>
            <span style="background: #0284c7; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 6px;">
                Cabang: {{ $currentEmployee->branch->name ?? 'Toko' }}
            </span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($pendingStoreTransfers as $trf)
                @php
                    $bp = \Modules\MasterData\Domain\Models\BranchProduct::where('branch_id', $currentEmployee->branch_id)
                        ->where('product_variant_id', $trf->product_variant_id)
                        ->first();
                    $binLocation = $bp->bin_location ?? 'Gudang Depan Toko';
                @endphp
                <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 10px; padding: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div>
                            <span style="font-size: 10.5px; color: #94a3b8; font-family: monospace;">No. Surat Jalan DC:</span>
                            <strong style="font-family: monospace; color: #38bdf8; font-size: 13px; margin-left: 4px;">{{ $trf->transfer_code }}</strong>
                            <div style="font-size: 11px; color: #cbd5e1; margin-top: 2px;">
                                Pengirim: <strong>{{ $trf->fromWarehouse->branch->name ?? $trf->fromWarehouse->name ?? 'DC Pusat' }}</strong> • {{ $trf->dispatched_at ? $trf->dispatched_at->format('d/m/Y H:i') : '-' }} WIB
                            </div>
                        </div>
                        <span style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 10.5px; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                            In-Transit
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px; margin-bottom: 12px; font-size: 12px;">
                        <div>
                            <div style="font-weight: 700; color: #f8fafc; font-size: 13.5px;">{{ $trf->variant->name ?? 'Item Produk' }}</div>
                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">SKU: {{ $trf->variant->sku ?? '-' }}</div>
                            <div style="margin-top: 3px; font-size: 11px; color: #38bdf8;">
                                📍 Rak Penempatan Toko: <strong>{{ $binLocation }}</strong>
                            </div>
                        </div>
                        <div style="text-align: right; background: rgba(30, 41, 59, 0.6); padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(51, 65, 85, 0.5);">
                            <div style="font-size: 10.5px; color: #94a3b8;">Kuantitas Dikirim:</div>
                            <div style="font-size: 18px; font-weight: 800; font-family: monospace; color: #f8fafc;">
                                {{ $trf->qty }} Unit
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('presensi2.transfer.terima', $trf->id) }}" style="display: grid; grid-template-columns: 140px 1fr auto; gap: 10px; align-items: flex-end; background: rgba(30, 41, 59, 0.8); padding: 10px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.2);">
                        @csrf
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: #cbd5e1; display: block; margin-bottom: 3px;">Qty Diterima Fisik:</label>
                            <input type="number" name="qty_received" value="{{ $trf->qty }}" min="0" required style="width: 100%; padding: 6px 8px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 4px; color: #fff; font-family: monospace; font-weight: bold; font-size: 12.5px;">
                        </div>
                        <div>
                            <label style="font-size: 10.5px; font-weight: 700; color: #cbd5e1; display: block; margin-bottom: 3px;">Catatan Kondisi Barang / Rak:</label>
                            <input type="text" name="notes" placeholder="Kondisi baik, langsung simpan di {{ $binLocation }}" style="width: 100%; padding: 6px 8px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 4px; color: #fff; font-size: 11.5px;">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-emerald" style="padding: 7px 16px; font-size: 12px; font-weight: 700; white-space: nowrap; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                <span>✅</span> Konfirmasi Masuk Rak
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <form action="{{ route('presensi2.stok.store') }}" method="POST">
        @csrf

        {{-- BILAH PANDUAN TAHAPAN: TAHAP 1 (STOK & HARDWARE) -> TAHAP 2 (KAS LACI) --}}
        <div class="glass-card" style="padding: 12px 16px; margin-bottom: 20px; border-radius: 14px; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(56, 189, 248, 0.3);">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                {{-- TAB TAHAP 1 --}}
                <div id="step-tab-1" onclick="goToStep1()" style="cursor: pointer; padding: 12px 16px; border-radius: 10px; background: rgba(14, 165, 233, 0.18); border: 1.5px solid #0284c7; display: flex; align-items: center; gap: 12px; transition: all 0.2s;">
                    <div id="step-tab-1-icon" style="width: 36px; height: 36px; border-radius: 8px; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0;">
                        1
                    </div>
                    <div>
                        <div style="font-size: 13.5px; font-weight: 800; color: #f8fafc;" id="step-tab-1-title">
                            Tahap 1: Cek Stok Barang & Hardware
                        </div>
                        <div style="font-size: 11px; color: #bae6fd;" id="step-tab-1-sub">
                            Kertas Print, Minuman, Makanan, & 15 PC Station
                        </div>
                    </div>
                </div>

                {{-- TAB TAHAP 2 --}}
                <div id="step-tab-2" onclick="goToStep2(false)" style="cursor: pointer; padding: 12px 16px; border-radius: 10px; background: rgba(255, 255, 255, 0.04); border: 1.5px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; gap: 12px; transition: all 0.2s;">
                    <div id="step-tab-2-icon" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255, 255, 255, 0.12); color: #94a3b8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0;">
                        2
                    </div>
                    <div>
                        <div style="font-size: 13.5px; font-weight: 800; color: #94a3b8;" id="step-tab-2-title">
                            Tahap 2: Konfirmasi Kas & Laci Shift
                        </div>
                        <div style="font-size: 11px; color: #64748b;" id="step-tab-2-sub">
                            Uang Masuk dari Shift Sebelumnya & Modal Laci
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- WADAH TAHAP 1: PEMERIKSAAN STOK FISIK BARANG & KELAYAKAN HARDWARE --}}
        <div id="step-1-container">

        {{-- PERINGATAN DINI STOK KRITIS / MENIPIS --}}
        @if(($stockComparisons['critical_count'] ?? 0) > 0)
        <div class="glass-card" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); padding: 14px 18px; margin-bottom: 16px; border-radius: 12px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(239, 68, 68, 0.25); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                        ⚠️
                    </div>
                    <div>
                        <div style="font-size: 13.5px; font-weight: 800; color: #fca5a5;">
                            Peringatan Dini: Terdapat {{ $stockComparisons['critical_count'] }} Item Stok Kritis / Menipis!
                        </div>
                        <div style="font-size: 11.5px; color: #fecaca; margin-top: 2px;">
                            Stok berada di bawah batas minimum operasional cabang. Klik tombol <strong>⚡ Restock Cepat</strong> untuk mengajukan penambahan barang ke Gudang/Supervisor sebelum shift berganti.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    @foreach(array_slice($stockComparisons['critical_items'] ?? [], 0, 4) as $crit)
                        <button type="button" onclick="openQuickRestockModal('{{ $crit['category'] }}', '{{ $crit['name'] }}', {{ $crit['suggested_qty'] }}, '{{ $crit['suggested_unit'] }}', '{{ $crit['urgency'] }}')" class="btn" style="background: rgba(239, 68, 68, 0.25); border: 1px solid rgba(239, 68, 68, 0.6); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; cursor: pointer;">
                            ⚡ {{ $crit['name'] }} ({{ $crit['remaining'] }})
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- 1, 2, & 3. KARTU TERPADU BER-TAB: STOK KERTAS, MINUMAN, & MAKANAN --}}
        <div class="glass-card" style="padding: 20px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        📦
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">1–3. Pemeriksaan Stok Kertas, Minuman & Makanan</h3>
                        <span style="font-size: 11px; color: var(--text-muted);">Pemeriksaan stok sistem vs fisik aktual di kulkas/rak. Masukkan kuantitas fisik riil untuk verifikasi selisih & keaslian serah terima shift.</span>
                    </div>
                </div>

                {{-- TAB SWITCHER RINGKAS --}}
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <button type="button" onclick="switchStokAwalTab('tab-awal-kertas', this)" class="btn-stok-awal-tab active" style="padding: 7px 14px; font-size: 11.5px; font-weight: 700; border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.5); background: rgba(59, 130, 246, 0.25); color: #fff; cursor: pointer;">
                        📄 1. Stok Kertas Print
                    </button>
                    <button type="button" onclick="switchStokAwalTab('tab-awal-minuman', this)" class="btn-stok-awal-tab" style="padding: 7px 14px; font-size: 11.5px; font-weight: 700; border-radius: 8px; border: 1px solid var(--border-glass); background: rgba(15, 23, 42, 0.6); color: var(--text-muted); cursor: pointer;">
                        🥤 2. Stok Minuman ({{ count($stockComparisons['minuman'] ?? []) }})
                    </button>
                    <button type="button" onclick="switchStokAwalTab('tab-awal-makanan', this)" class="btn-stok-awal-tab" style="padding: 7px 14px; font-size: 11.5px; font-weight: 700; border-radius: 8px; border: 1px solid var(--border-glass); background: rgba(15, 23, 42, 0.6); color: var(--text-muted); cursor: pointer;">
                        🍜 3. Stok Makanan ({{ count($stockComparisons['makanan'] ?? []) }})
                    </button>
                </div>
            </div>

            {{-- BANNER SERAH TERIMA FISIK DARI SHIFT SEBELUMNYA --}}
            <div style="background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(139, 92, 246, 0.35); border-radius: 10px; padding: 10px 14px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 10px; font-size: 11.5px; color: #cbd5e1;">
                    <span style="font-size: 18px;">⏮️</span>
                    <div>
                        <span>Serah Terima Fisik dari <strong>{{ $prevShiftBilling['name'] }}</strong> &bull; Operator Penyerah: <strong style="color: #c4b5fd;">{{ $prevShiftBilling['operator_name'] }}</strong></span>
                        <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">
                            Data sisa fisik riil yang diserahkan shift sebelumnya ditampilkan pada kolom <strong>Stok Shift Sebelumnya</strong>. Kasir shift baru hanya bertanggung jawab atas selisih di shift berjalan.
                        </div>
                    </div>
                </div>
                @if(!empty($prevShiftBilling['checklist']?->drink_notes) || !empty($prevShiftBilling['checklist']?->food_notes))
                <div style="font-size: 11px; color: #fde68a; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 6px; padding: 4px 10px;">
                    📝 <strong>Catatan Shift Lalu:</strong> {{ $prevShiftBilling['checklist']?->drink_notes ?: $prevShiftBilling['checklist']?->food_notes }}
                </div>
                @endif
            </div>

            {{-- TAB 1: STOK KERTAS PRINT --}}
            <div id="tab-awal-kertas" class="stok-awal-tab-pane">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-glass); color: var(--text-muted);">
                                <th style="padding: 8px 10px;">Jenis Kertas</th>
                                <th style="padding: 8px 10px; text-align: center;">Stok Shift Sebelumnya</th>
                                <th style="padding: 8px 10px; text-align: center;">Penjualan / Terpakai Hari Ini</th>
                                <th style="padding: 8px 10px; text-align: center;">Sisa Stok Sistem</th>
                                <th style="padding: 8px 10px; text-align: center; color: #38bdf8;">Fisik Aktual (Laci/Meja)</th>
                                <th style="padding: 8px 10px; text-align: center;">Selisih</th>
                                <th style="padding: 8px 10px; text-align: right;">Status & Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockComparisons['kertas'] ?? [] as $k)
                            @php 
                                $isCrit = (bool) ($k['is_critical'] ?? false); 
                                $kId = $k['id'];
                                $physVal = old('paper_physical.'.$kId, $k['physical_stock'] ?? $k['sesudahnya']);
                                $diffVal = (int) $physVal - (int) $k['sesudahnya'];
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="padding: 10px; color: #fff; font-weight: 600;">
                                    {{ $k['name'] }}
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <div style="color: #93c5fd; font-weight: 700;">
                                        {{ $k['sebelumnya'] }} <span style="font-size: 10px; color: var(--text-muted);">{{ $k['unit'] }}</span>
                                    </div>
                                    @if(isset($k['prev_physical']) && $k['prev_physical'] !== null)
                                        <div style="font-size: 9.5px; color: #c4b5fd; margin-top: 2px; background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 4px; padding: 1px 4px; display: inline-block;">
                                            ⏮️ Fisik: <strong>{{ $k['prev_physical'] }}</strong>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 10px; text-align: center; color: #fbbf24; font-weight: 700;">
                                    {{ $k['terjual'] > 0 ? '-'.$k['terjual'] : '0' }} <span style="font-size: 10px; color: var(--text-muted);">{{ $k['unit'] }}</span>
                                </td>
                                <td style="padding: 10px; text-align: center; color: {{ $isCrit ? '#f87171' : '#34d399' }}; font-weight: 700;">
                                    {{ $k['sesudahnya'] }} <span style="font-size: 10px; color: var(--text-muted);">{{ $k['unit'] }}</span>
                                </td>
                                <td style="padding: 6px 10px; text-align: center;">
                                    <input type="number" 
                                           name="paper_physical[{{ $kId }}]" 
                                           class="stock-physical-input"
                                           data-system="{{ $k['sesudahnya'] }}"
                                           data-critical-threshold="100"
                                           data-unit="{{ $k['unit'] }}"
                                           data-category="kertas"
                                           data-target-diff="kertas-diff-{{ $kId }}"
                                           data-target-status="kertas-status-{{ $kId }}"
                                           value="{{ $physVal }}" 
                                           min="0" step="1"
                                           style="width: 75px; padding: 4px 6px; text-align: center; font-weight: 700; font-size: 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 6px; color: #fff; outline: none;"
                                           oninput="recalculateStockRow(this)">
                                </td>
                                <td style="padding: 6px 10px; text-align: center;">
                                    <span id="kertas-diff-{{ $kId }}">
                                        @if($diffVal === 0)
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 10.5px; padding: 2px 7px;">0 {{ $k['unit'] }}</span>
                                        @elseif($diffVal < 0)
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); font-size: 10.5px; padding: 2px 7px; font-weight: 800;">{{ $diffVal }} {{ $k['unit'] }}</span>
                                        @else
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.45); font-size: 10.5px; padding: 2px 7px; font-weight: 800;">+{{ $diffVal }} {{ $k['unit'] }}</span>
                                        @endif
                                    </span>
                                </td>
                                <td style="padding: 10px; text-align: right;">
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                        <span id="kertas-status-{{ $kId }}">
                                            @if($diffVal < 0)
                                                <span class="badge" style="background: rgba(239, 68, 68, 0.25); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.5); font-size: 10px; font-weight: 700;">⚠️ Selisih Kurang</span>
                                            @elseif($diffVal > 0)
                                                <span class="badge" style="background: rgba(59, 130, 246, 0.25); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.5); font-size: 10px; font-weight: 700;">ℹ️ Selisih Lebih</span>
                                            @else
                                                <span class="badge {{ $isCrit ? '' : 'badge-present' }}" style="{{ $isCrit ? 'background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4);' : 'background: rgba(16, 185, 129, 0.15); color: #34d399;' }} font-size: 10px;">
                                                    {{ $isCrit ? '⚠️ Kritis' : '✓ Aman (Cocok)' }}
                                                </span>
                                            @endif
                                        </span>
                                        <span class="badge" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); color: #94a3b8; font-size: 10px;">
                                            {{ $k['rim_info'] }}
                                        </span>
                                        <button type="button" onclick="openQuickRestockModal('kertas', '{{ $k['name'] }}', 5, 'Rim', '{{ $isCrit ? 'high' : 'normal' }}')" class="btn" style="padding: 4px 9px; font-size: 10.5px; font-weight: 700; background: {{ $isCrit ? 'rgba(239, 68, 68, 0.25)' : 'rgba(14, 165, 233, 0.2)' }}; border: 1px solid {{ $isCrit ? 'rgba(239, 68, 68, 0.5)' : 'rgba(14, 165, 233, 0.4)' }}; color: {{ $isCrit ? '#fca5a5' : '#38bdf8' }}; border-radius: 6px; cursor: pointer;">
                                            ⚡ Restock
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="kertas-discrepancy-banner" style="display: none; margin-top: 10px; padding: 10px 14px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 10px; font-size: 11.5px; color: #fca5a5;"></div>
                <input type="hidden" name="paper_a4" value="1">
                <input type="hidden" name="paper_a4_qty" value="{{ $stockComparisons['kertas'][0]['sebelumnya'] ?? 500 }}">
                <input type="hidden" name="paper_f4" value="1">
                <input type="hidden" name="paper_f4_qty" value="{{ $stockComparisons['kertas'][1]['sebelumnya'] ?? 500 }}">
            </div>

            {{-- TAB 2: STOK MINUMAN --}}
            <div id="tab-awal-minuman" class="stok-awal-tab-pane" style="display: none;">
                <div style="overflow-x: auto; max-height: 350px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-glass); color: var(--text-muted); position: sticky; top: 0; background: #0f172a; z-index: 2;">
                                <th style="padding: 8px 10px;">Nama Minuman</th>
                                <th style="padding: 8px 10px; text-align: center;">Stok Shift Sebelumnya</th>
                                <th style="padding: 8px 10px; text-align: center;">Penjualan Hari Ini</th>
                                <th style="padding: 8px 10px; text-align: center;">Sisa Stok Sistem</th>
                                <th style="padding: 8px 10px; text-align: center; color: #38bdf8;">Fisik Aktual (Kulkas/Rak)</th>
                                <th style="padding: 8px 10px; text-align: center;">Selisih</th>
                                <th style="padding: 8px 10px; text-align: right;">Status & Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockComparisons['minuman'] ?? [] as $d)
                            @php 
                                $isCrit = (bool) ($d['is_critical'] ?? ($d['sesudahnya'] <= 5)); 
                                $dId = $d['variant_id'];
                                $physVal = old('drink_physical.'.$dId, $d['physical_stock'] ?? $d['sesudahnya']);
                                $diffVal = (int) $physVal - (int) $d['sesudahnya'];
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="padding: 9px 10px; color: #fff; font-weight: 600;">
                                    {{ $d['name'] }}
                                </td>
                                <td style="padding: 9px 10px; text-align: center;">
                                    <div style="color: #93c5fd; font-weight: 700;">
                                        {{ $d['sebelumnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                    </div>
                                    @if(isset($d['prev_physical']) && $d['prev_physical'] !== null)
                                        <div style="font-size: 9.5px; color: #c4b5fd; margin-top: 2px; background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 4px; padding: 1px 4px; display: inline-block;">
                                            ⏮️ Fisik: <strong>{{ $d['prev_physical'] }}</strong>
                                            @if(($d['prev_diff'] ?? 0) < 0)
                                                <span style="color: #f87171; font-weight: 800;">({{ $d['prev_diff'] }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: #fbbf24; font-weight: 700;">
                                    {{ $d['terjual'] > 0 ? '-'.$d['terjual'] : '0' }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: {{ $isCrit ? '#f87171' : '#34d399' }}; font-weight: 700;">
                                    {{ $d['sesudahnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 6px 10px; text-align: center;">
                                    <input type="number" 
                                           name="drink_physical[{{ $dId }}]" 
                                           class="stock-physical-input"
                                           data-system="{{ $d['sesudahnya'] }}"
                                           data-critical-threshold="5"
                                           data-unit="pcs"
                                           data-category="minuman"
                                           data-target-diff="drink-diff-{{ $dId }}"
                                           data-target-status="drink-status-{{ $dId }}"
                                           value="{{ $physVal }}" 
                                           min="0" step="1"
                                           style="width: 70px; padding: 4px 6px; text-align: center; font-weight: 700; font-size: 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 6px; color: #fff; outline: none;"
                                           oninput="recalculateStockRow(this)">
                                </td>
                                <td style="padding: 6px 10px; text-align: center;">
                                    <span id="drink-diff-{{ $dId }}">
                                        @if($diffVal === 0)
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 10.5px; padding: 2px 7px;">0 pcs</span>
                                        @elseif($diffVal < 0)
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); font-size: 10.5px; padding: 2px 7px; font-weight: 800;">{{ $diffVal }} pcs</span>
                                        @else
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.45); font-size: 10.5px; padding: 2px 7px; font-weight: 800;">+{{ $diffVal }} pcs</span>
                                        @endif
                                    </span>
                                </td>
                                <td style="padding: 9px 10px; text-align: right;">
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                        <span id="drink-status-{{ $dId }}">
                                            @if($diffVal < 0)
                                                <span class="badge" style="background: rgba(239, 68, 68, 0.25); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.5); font-size: 10px; font-weight: 700;">⚠️ Selisih Kurang</span>
                                            @elseif($diffVal > 0)
                                                <span class="badge" style="background: rgba(59, 130, 246, 0.25); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.5); font-size: 10px; font-weight: 700;">ℹ️ Selisih Lebih</span>
                                            @else
                                                @if($isCrit)
                                                    <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 10px;">⚠️ Kritis</span>
                                                @else
                                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px;">✓ Aman (Cocok)</span>
                                                @endif
                                            @endif
                                        </span>
                                        <button type="button" onclick="openQuickRestockModal('minuman', '{{ $d['name'] }}', 1, 'Dus', '{{ $isCrit ? 'high' : 'normal' }}')" class="btn" style="padding: 3px 8px; font-size: 10px; font-weight: 700; background: {{ $isCrit ? 'rgba(239,68,68,0.25)' : 'rgba(59,130,246,0.18)' }}; border: 1px solid {{ $isCrit ? 'rgba(239,68,68,0.5)' : 'rgba(59,130,246,0.35)' }}; color: {{ $isCrit ? '#fca5a5' : '#93c5fd' }}; border-radius: 5px; cursor: pointer;">
                                            ⚡ Restock
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="minuman-discrepancy-banner" style="display: none; margin-top: 10px; padding: 10px 14px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 10px; font-size: 11.5px; color: #fca5a5;"></div>
                <div style="margin-top: 10px;">
                    <input type="text" name="drink_notes" value="{{ old('drink_notes', $existingChecklist->drink_notes ?? '') }}" placeholder="Catatan tambahan minuman (misal: Aqua kurang 1 botol dari shift sebelumnya, sirup habis)..." style="width: 100%; padding: 9px 12px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 10px; color: #fff; font-size: 12px;">
                </div>
            </div>

            {{-- TAB 3: STOK MAKANAN & SNACK --}}
            <div id="tab-awal-makanan" class="stok-awal-tab-pane" style="display: none;">
                <div style="overflow-x: auto; max-height: 350px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-glass); color: var(--text-muted); position: sticky; top: 0; background: #0f172a; z-index: 2;">
                                <th style="padding: 8px 10px;">Nama Makanan / Snack</th>
                                <th style="padding: 8px 10px; text-align: center;">Stok Shift Sebelumnya</th>
                                <th style="padding: 8px 10px; text-align: center;">Penjualan Hari Ini</th>
                                <th style="padding: 8px 10px; text-align: center;">Sisa Stok Sistem</th>
                                <th style="padding: 8px 10px; text-align: center; color: #38bdf8;">Fisik Aktual (Rak/Etalase)</th>
                                <th style="padding: 8px 10px; text-align: center;">Selisih</th>
                                <th style="padding: 8px 10px; text-align: right;">Status & Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockComparisons['makanan'] ?? [] as $m)
                            @php 
                                $isCrit = (bool) ($m['is_critical'] ?? ($m['sesudahnya'] <= 5)); 
                                $mId = $m['variant_id'];
                                $physVal = old('food_physical.'.$mId, $m['physical_stock'] ?? $m['sesudahnya']);
                                $diffVal = (int) $physVal - (int) $m['sesudahnya'];
                            @endphp
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="padding: 9px 10px; color: #fff; font-weight: 600;">
                                    {{ $m['name'] }}
                                </td>
                                <td style="padding: 9px 10px; text-align: center;">
                                    <div style="color: #93c5fd; font-weight: 700;">
                                        {{ $m['sebelumnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                    </div>
                                    @if(isset($m['prev_physical']) && $m['prev_physical'] !== null)
                                        <div style="font-size: 9.5px; color: #c4b5fd; margin-top: 2px; background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 4px; padding: 1px 4px; display: inline-block;">
                                            ⏮️ Fisik: <strong>{{ $m['prev_physical'] }}</strong>
                                            @if(($m['prev_diff'] ?? 0) < 0)
                                                <span style="color: #f87171; font-weight: 800;">({{ $m['prev_diff'] }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: #fbbf24; font-weight: 700;">
                                    {{ $m['terjual'] > 0 ? '-'.$m['terjual'] : '0' }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: {{ $isCrit ? '#f87171' : '#34d399' }}; font-weight: 700;">
                                    {{ $m['sesudahnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 6px 10px; text-align: center;">
                                    <input type="number" 
                                           name="food_physical[{{ $mId }}]" 
                                           class="stock-physical-input"
                                           data-system="{{ $m['sesudahnya'] }}"
                                           data-critical-threshold="5"
                                           data-unit="pcs"
                                           data-category="makanan"
                                           data-target-diff="food-diff-{{ $mId }}"
                                           data-target-status="food-status-{{ $mId }}"
                                           value="{{ $physVal }}" 
                                           min="0" step="1"
                                           style="width: 70px; padding: 4px 6px; text-align: center; font-weight: 700; font-size: 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 6px; color: #fff; outline: none;"
                                           oninput="recalculateStockRow(this)">
                                </td>
                                <td style="padding: 6px 10px; text-align: center;">
                                    <span id="food-diff-{{ $mId }}">
                                        @if($diffVal === 0)
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 10.5px; padding: 2px 7px;">0 pcs</span>
                                        @elseif($diffVal < 0)
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); font-size: 10.5px; padding: 2px 7px; font-weight: 800;">{{ $diffVal }} pcs</span>
                                        @else
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.45); font-size: 10.5px; padding: 2px 7px; font-weight: 800;">+{{ $diffVal }} pcs</span>
                                        @endif
                                    </span>
                                </td>
                                <td style="padding: 9px 10px; text-align: right;">
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                        <span id="food-status-{{ $mId }}">
                                            @if($diffVal < 0)
                                                <span class="badge" style="background: rgba(239, 68, 68, 0.25); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.5); font-size: 10px; font-weight: 700;">⚠️ Selisih Kurang</span>
                                            @elseif($diffVal > 0)
                                                <span class="badge" style="background: rgba(59, 130, 246, 0.25); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.5); font-size: 10px; font-weight: 700;">ℹ️ Selisih Lebih</span>
                                            @else
                                                @if($isCrit)
                                                    <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 10px;">⚠️ Kritis</span>
                                                @else
                                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px;">✓ Aman (Cocok)</span>
                                                @endif
                                            @endif
                                        </span>
                                        <button type="button" onclick="openQuickRestockModal('makanan', '{{ $m['name'] }}', 1, 'Dus', '{{ $isCrit ? 'high' : 'normal' }}')" class="btn" style="padding: 3px 8px; font-size: 10px; font-weight: 700; background: {{ $isCrit ? 'rgba(239,68,68,0.25)' : 'rgba(59,130,246,0.18)' }}; border: 1px solid {{ $isCrit ? 'rgba(239,68,68,0.5)' : 'rgba(59,130,246,0.35)' }}; color: {{ $isCrit ? '#fca5a5' : '#93c5fd' }}; border-radius: 5px; cursor: pointer;">
                                            ⚡ Restock
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="makanan-discrepancy-banner" style="display: none; margin-top: 10px; padding: 10px 14px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 10px; font-size: 11.5px; color: #fca5a5;"></div>
                <div style="margin-top: 10px;">
                    <input type="text" name="food_notes" value="{{ old('food_notes', $existingChecklist->food_notes ?? '') }}" placeholder="Catatan tambahan makanan (misal: Chiki kurang 1 pcs dari shift sebelumnya, telur aman)..." style="width: 100%; padding: 9px 12px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 10px; color: #fff; font-size: 12px;">
                </div>
            </div>

            {{-- KARTU RINGKASAN & TOMBOL AKSI SERAH TERIMA STOK KE SHIFT SELANJUTNYA & ADMIN --}}
            <div id="stock-handover-summary-box" style="margin-top: 18px; padding: 16px; background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(59, 130, 246, 0.35); border-radius: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; border-bottom: 1px dashed rgba(255, 255, 255, 0.1); padding-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 20px;">📦</span>
                        <div>
                            <h4 style="font-size: 13.5px; font-weight: 700; color: #fff; margin: 0;">
                                Berita Acara Rekonsiliasi Stok Fisik Antar-Shift
                            </h4>
                            <span style="font-size: 11px; color: var(--text-muted);">
                                Otomatis disinkronkan ke Operator Shift Sebelumnya (<strong style="color: #c4b5fd;">{{ $prevShiftBilling['operator_name'] }}</strong>) & Laporan Audit Admin
                            </span>
                        </div>
                    </div>
                    <div id="stock-handover-status-badge" style="display: flex; align-items: center; gap: 6px;">
                        <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 11px; padding: 4px 10px; font-weight: 700;">
                            ✓ Semua Fisik Sesuai Sistem
                        </span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin-bottom: 14px; font-size: 11.5px;">
                    <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 8px 12px;">
                        <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Total Item Diperiksa:</span>
                        <strong id="stat-total-items" style="color: #fff; font-size: 14px; font-family: monospace;">{{ count($stockComparisons['kertas'] ?? []) + count($stockComparisons['minuman'] ?? []) + count($stockComparisons['makanan'] ?? []) }}</strong> Item
                    </div>
                    <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 8px 12px;">
                        <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Fisik Cocok:</span>
                        <strong id="stat-match-items" style="color: #34d399; font-size: 14px; font-family: monospace;">0</strong> Item
                    </div>
                    <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 8px 12px;">
                        <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Selisih Kurang:</span>
                        <strong id="stat-minus-items" style="color: #f87171; font-size: 14px; font-family: monospace;">0</strong> Item
                    </div>
                    <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 8px 12px;">
                        <span style="color: var(--text-muted); display: block; font-size: 10.5px;">Selisih Lebih:</span>
                        <strong id="stat-plus-items" style="color: #60a5fa; font-size: 14px; font-family: monospace;">0</strong> Item
                    </div>
                </div>

                {{-- TOMBOL AKSI SERAH STOK & LANJUT KE KAS --}}
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="font-size: 11px; color: var(--text-sub); display: flex; align-items: center; gap: 6px;">
                        <span>🔒</span>
                        <span>Selisih yang dilaporkan akan tercatat atas nama Shift Sebelumnya tanpa memotong ganti rugi kasir shift ini.</span>
                    </div>
                    <button type="button" id="btn-confirm-stock-handover" onclick="confirmStockHandoverAndProceed()" class="btn" style="padding: 10px 20px; font-size: 12.5px; font-weight: 700; background: linear-gradient(135deg, #059669, #10b981); color: #fff; border: 1px solid rgba(16, 185, 129, 0.5); border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                        <span id="btn-confirm-stock-icon">✅</span>
                        <span id="btn-confirm-stock-text">Konfirmasi Serah Terima Stok & Lanjut ke Cek Kas Laci ⬇️</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- 4. CEK HARDWARE --}}
        <div class="glass-card" style="padding: 20px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(139, 92, 246, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        🖥️
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">4. Cek Kelayakan Hardware</h3>
                        <span style="font-size: 11px; color: var(--text-muted);">Pemeriksaan PC Client, Printer, Jaringan & AC</span>
                    </div>
                </div>
                <div>
                    <select name="hardware_status" style="padding: 6px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(139, 92, 246, 0.4); border-radius: 8px; color: #fff; font-size: 12px; font-weight: 600; outline: none;">
                        <option value="normal" {{ old('hardware_status', $existingChecklist->hardware_status ?? 'normal') === 'normal' ? 'selected' : '' }}>🟢 Semua Normal & Berfungsi</option>
                        <option value="warning" {{ old('hardware_status', $existingChecklist->hardware_status ?? '') === 'warning' ? 'selected' : '' }}>🟡 Ada Kendala Ringan</option>
                        <option value="issue" {{ old('hardware_status', $existingChecklist->hardware_status ?? '') === 'issue' ? 'selected' : '' }}>🔴 Perlu Maintenance Segera</option>
                    </select>
                </div>
            </div>

            {{-- INFORMASI KELAYAKAN HARDWARE DARI SHIFT SEBELUMNYA --}}
            @if(!empty($prevShiftBilling['checklist']?->hardware_notes) || ($prevShiftBilling['checklist']?->hardware_status ?? 'normal') !== 'normal')
            <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 10px; padding: 10px 14px; margin-bottom: 12px; font-size: 11.5px; color: #fde68a; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">🖥️</span>
                    <div>
                        <strong>Catatan Hardware Shift Lalu ({{ $prevShiftBilling['name'] }} &bull; {{ $prevShiftBilling['operator_name'] }}):</strong>
                        <span style="color: #fff; margin-left: 4px;">{{ $prevShiftBilling['checklist']?->hardware_notes ?: 'Terdapat catatan kendala hardware peninggalan shift sebelumnya.' }}</span>
                    </div>
                </div>
                <span class="badge" style="background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(245, 158, 11, 0.5); color: #fbbf24; font-size: 10px; font-weight: 700; padding: 2px 7px;">
                    Status Lalu: {{ strtoupper($prevShiftBilling['checklist']?->hardware_status ?? 'warning') }}
                </span>
            </div>
            @endif

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; margin-bottom: 12px;">
                @php
                    $hardwareItems = [
                        'pc_stations' => '15 Unit PC Client (Monitor, Keyboard, Mouse, Headset)',
                        'server_billing' => 'Server Billing & Komputer Kasir Utama',
                        'printer_thermal' => 'Printer Kasir Thermal Struk EPSON',
                        'printer_hvs' => 'Printer HVS Epson L3210 (A4 & F4)',
                        'lan_network' => 'Kabel LAN, Switch Gigabit & WiFi Router',
                        'cctv_ac' => 'CCTV 8-Channel & AC Ruangan Berfungsi Normal',
                    ];
                @endphp
                @foreach($hardwareItems as $key => $title)
                <label style="display: flex; align-items: center; gap: 10px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 10px; padding: 10px 12px; cursor: pointer;">
                    <input type="checkbox" name="hardware_checklist[]" value="{{ $key }}" checked style="width: 16px; height: 16px; accent-color: #8b5cf6;">
                    <span style="font-size: 12px; font-weight: 600; color: #e5e7eb;">{{ $title }}</span>
                </label>
                @endforeach
            </div>

            <input type="text" name="hardware_notes" value="{{ old('hardware_notes', $existingChecklist->hardware_notes ?? '') }}" placeholder="Catatan hardware (misal: PC-04 headset kiri kresek-kresek, PC-09 bersih)..." style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 10px; color: #fff; font-size: 12px;">
        </div>

        {{-- PROGRES SETTLEMENT 4 SHIFT OPERASIONAL HARI INI --}}
        <div class="glass-card" style="padding: 20px; margin-bottom: 20px; border-color: rgba(6, 182, 212, 0.35);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(6, 182, 212, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        📊
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">Status Settlement 4 Shift Hari Ini</h3>
                        <span style="font-size: 11px; color: var(--text-muted);">Akumulasi omset harian & kontrol serah terima kasir 24 jam (Shift 1 s/d Shift 4)</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 11px; padding: 5px 12px; border: 1px solid rgba(16, 185, 129, 0.3);">
                        Progres: <strong>{{ $billingData['settled_count'] ?? 0 }} / {{ $billingData['total_shifts'] ?? 4 }}</strong> Shift Ditutup
                    </span>
                </div>
            </div>

            {{-- 4 KARTU GRID SHIFT --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 14px;">
                @foreach($billingData['shifts'] ?? [] as $shiftItem)
                @php
                    $isCur = (bool) ($shiftItem['is_current'] ?? false);
                    $isSet = (bool) ($shiftItem['is_settled'] ?? false);
                    $borderColor = $isCur ? 'rgba(56, 189, 248, 0.6)' : ($isSet ? 'rgba(16, 185, 129, 0.4)' : 'rgba(255, 255, 255, 0.08)');
                    $bgColor = $isCur ? 'rgba(14, 165, 233, 0.1)' : ($isSet ? 'rgba(16, 185, 129, 0.06)' : 'rgba(15, 23, 42, 0.5)');
                @endphp
                <div style="background: {{ $bgColor }}; border: 1px solid {{ $borderColor }}; border-radius: 12px; padding: 14px; position: relative;">
                    @if($isCur)
                        <div style="position: absolute; top: -8px; right: 10px; background: #0284c7; color: #fff; font-size: 9.5px; font-weight: 800; padding: 2px 7px; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.04em;">
                            Shift Anda
                        </div>
                    @endif
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #fff;">{{ $shiftItem['name'] }}</span>
                        <span style="font-size: 10px; color: var(--text-muted); font-family: monospace;">{{ $shiftItem['time_range'] }}</span>
                    </div>

                    <div style="margin-bottom: 8px;">
                        <span class="badge" style="font-size: 10px; font-weight: 700; color: {{ $shiftItem['status_color'] }}; background: rgba(0, 0, 0, 0.3); border: 1px solid {{ $shiftItem['status_color'] }}44; padding: 2px 8px; border-radius: 6px;">
                            {{ $shiftItem['status_label'] }}
                        </span>
                    </div>

                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">
                        Kasir: <strong style="color: #e2e8f0;">{{ $shiftItem['operator_name'] ?? 'Belum Ditugaskan' }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(255, 255, 255, 0.1);">
                        <div>
                            <span style="font-size: 10px; color: var(--text-muted); display: block;">Omset Billing:</span>
                            <span style="font-size: 13px; font-weight: 800; color: #38bdf8; font-family: monospace;">
                                Rp {{ number_format($shiftItem['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                        @if($isSet && $shiftItem['deposited_amount'] !== null)
                        <div style="text-align: right;">
                            <span style="font-size: 10px; color: var(--text-muted); display: block;">Kas Fisik:</span>
                            <span style="font-size: 12px; font-weight: 700; color: #34d399; font-family: monospace;">
                                Rp {{ number_format($shiftItem['deposited_amount'], 0, ',', '.') }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- RINGKASAN AKUMULASI HARIAN --}}
            <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">📈</span>
                    <span style="font-size: 12px; color: #cbd5e1; font-weight: 600;">
                        Akumulasi Omset Billing Hari Ini:
                    </span>
                    <strong style="font-size: 15px; color: #38bdf8; font-family: monospace;">
                        Rp {{ number_format($billingData['total_income'] ?? 0, 0, ',', '.') }}
                    </strong>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">💰</span>
                    <span style="font-size: 12px; color: #cbd5e1; font-weight: 600;">
                        Total Kas Fisik Disetor/Di Laci Hari Ini:
                    </span>
                    <strong style="font-size: 15px; color: #34d399; font-family: monospace;">
                        Rp {{ number_format($billingData['total_settled_cash'] ?? 0, 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            {{-- TOMBOL LANJUT KE TAHAP 2 --}}
            <div style="margin-top: 24px; text-align: center; padding: 22px; background: rgba(14, 165, 233, 0.06); border: 1.5px dashed rgba(14, 165, 233, 0.35); border-radius: 14px;">
                <div style="margin-bottom: 12px;">
                    <span style="font-size: 14px; font-weight: 800; color: #38bdf8; display: block; margin-bottom: 4px;">
                        📦 Pemeriksaan Stok & Hardware (Tahap 1) Selesai?
                    </span>
                    <span style="font-size: 11.5px; color: var(--text-muted); max-width: 580px; display: inline-block;">
                        Pastikan seluruh fisik kertas print, kulkas minuman, etalase makanan, dan kelayakan 15 PC telah diverifikasi sebelum melanjutkan ke tahap penghitungan uang kas laci.
                    </span>
                </div>
                <button type="button" onclick="goToStep2(true)" id="btn-next-to-step2" class="btn btn-emerald" style="padding: 15px 32px; font-size: 15px; font-weight: 800; width: 100%; max-width: 520px; margin: 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35); cursor: pointer;">
                    <span>Lanjut ke Tahap 2: Konfirmasi Kas & Serah Terima Laci</span>
                    <span style="font-size: 18px;">➡️</span>
                </button>
            </div>
        </div>
        </div>
        {{-- AKHIR WADAH TAHAP 1 --}}

        {{-- WADAH TAHAP 2: KONFIRMASI KAS & SERAH TERIMA UANG SHIFT SEBELUMNYA --}}
        <div id="step-2-container" style="display: none;">
            {{-- BANNER HEADER PANDUAN TAHAP 2 --}}
            <div class="glass-card" style="padding: 16px 20px; margin-bottom: 18px; border-radius: 12px; background: linear-gradient(135deg, rgba(6, 182, 212, 0.15), rgba(14, 165, 233, 0.08)); border: 1.5px solid rgba(6, 182, 212, 0.4);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #0284c7, #06b6d4); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.35);">
                            💰
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #f8fafc;">
                                Tahap 2: Konfirmasi Kas & Serah Terima Uang Laci
                            </h3>
                            <span style="font-size: 11.5px; color: #bae6fd;">
                                Pemeriksaan fisik stok barang & hardware (Tahap 1) telah selesai. Sekarang, hitung dan verifikasi fisik uang kas yang ada di laci kasir saat ini.
                            </span>
                        </div>
                    </div>
                    <div>
                        <button type="button" onclick="goToStep1()" class="btn" style="padding: 7px 14px; font-size: 11.5px; font-weight: 700; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #cbd5e1; border-radius: 6px; cursor: pointer;">
                            ⬅️ Cek Ulang Stok (Tahap 1)
                        </button>
                    </div>
                </div>
            </div>

            {{-- 5. KONFIRMASI KAS & SERAH TERIMA SHIFT SEBELUMNYA --}}
            <div class="glass-card" style="padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(6, 182, 212, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                            💰
                        </div>
                        <div>
                            <h3 style="font-size: 15px; font-weight: 700; color: #fff;">5. Konfirmasi Kas & Serah Terima Shift Sebelumnya</h3>
                            <span style="font-size: 11px; color: var(--text-muted);">Cek Daftar Billing Internet & verifikasi fisik uang kas masuk, modal laci kasir, dan pecahan uang dari operator shift sebelumnya</span>
                        </div>
                    </div>
                    <span class="badge" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee; font-size: 11px; padding: 4px 10px;">
                        Serah Terima Kas Masuk
                    </span>
                </div>

            {{-- KARTU KHUSUS RINCIAN SERAH TERIMA KAS DARI SHIFT SEBELUMNYA --}}
            <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(6, 182, 212, 0.35); border-radius: 14px; padding: 18px; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #38bdf8; font-weight: 700;">
                                ⏮️ Shift Sebelumnya yang Menyerahkan Kas:
                            </span>
                            @if(!empty($prevShiftBilling['is_deposited_to_safe']))
                                <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 10px; padding: 2px 8px; border-radius: 4px; border: 1px solid rgba(16, 185, 129, 0.4);">
                                    🔒 Omset Disetor ke Brankas / Bank
                                </span>
                            @else
                                <span class="badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8; font-size: 10px; padding: 2px 8px; border-radius: 4px; border: 1px solid rgba(14, 165, 233, 0.4);">
                                    🤝 Serah Estafet di Laci
                                </span>
                            @endif
                        </div>
                        <div style="font-size: 16px; font-weight: 700; color: #fff; margin-top: 2px;">
                            {{ $prevShiftBilling['name'] }} ({{ $prevShiftBilling['code'] }})
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            Jam Operasional: <strong style="color: #e2e8f0;">{{ $prevShiftBilling['time_range'] }}</strong> &bull; Tanggal: {{ $prevShiftBilling['work_date_formatted'] }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-sub); margin-top: 2px;">
                            Operator Penyerah: <strong style="color: #c4b5fd;">{{ $prevShiftBilling['operator_name'] }}</strong>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        @if(!empty($prevShiftBilling['is_deposited_to_safe']))
                            <div style="background: rgba(16, 185, 129, 0.1); padding: 8px 14px; border-radius: 10px; border: 1px solid rgba(16, 185, 129, 0.3); text-align: right;" title="Uang hasil penjualan yang sudah disetor/dimasukkan amplop brankas oleh kasir sebelumnya">
                                <span style="font-size: 10px; color: #a7f3d0; display: block;">🔒 Setor ke Brankas/Bank:</span>
                                <span style="font-size: 14px; font-weight: 700; color: #34d399; font-family: monospace;">
                                    Rp {{ number_format($prevShiftBilling['deposited_amount'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div style="background: rgba(245, 158, 11, 0.1); padding: 8px 14px; border-radius: 10px; border: 1px solid rgba(245, 158, 11, 0.3); text-align: right;">
                                <span style="font-size: 10px; color: #fde68a; display: block;">💰 Modal Laci Ditinggal:</span>
                                <span style="font-size: 14px; font-weight: 700; color: #fbbf24; font-family: monospace;">
                                    Rp {{ number_format($prevShiftBilling['retained_drawer_cash'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div style="background: rgba(6, 182, 212, 0.18); padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(6, 182, 212, 0.5); text-align: right;">
                                <span style="font-size: 10px; color: #67e8f9; display: block; font-weight: 700;">Wajib Ada di Laci Sekarang:</span>
                                <span style="font-size: 16px; font-weight: 800; color: #22d3ee; font-family: monospace;">
                                    Rp {{ number_format($prevShiftBilling['drawer_expected_cash'] ?? $prevShiftBilling['total_expected_cash'], 0, ',', '.') }}
                                </span>
                            </div>
                        @else
                            <div style="background: rgba(0, 0, 0, 0.35); padding: 8px 14px; border-radius: 10px; border: 1px solid rgba(56, 189, 248, 0.25); text-align: right;">
                                <span style="font-size: 10px; color: var(--text-muted); display: block;">Kas Diserahkan:</span>
                                <span style="font-size: 14px; font-weight: 700; color: #38bdf8; font-family: monospace;">
                                    Rp {{ number_format($prevShiftBilling['deposited_amount'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div style="background: rgba(0, 0, 0, 0.35); padding: 8px 14px; border-radius: 10px; border: 1px solid rgba(245, 158, 11, 0.25); text-align: right;">
                                <span style="font-size: 10px; color: var(--text-muted); display: block;">Modal Laci Kasir:</span>
                                <span style="font-size: 14px; font-weight: 700; color: #fbbf24; font-family: monospace;">
                                    Rp {{ number_format($prevShiftBilling['retained_drawer_cash'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div style="background: rgba(6, 182, 212, 0.15); padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(6, 182, 212, 0.4); text-align: right;">
                                <span style="font-size: 10px; color: #67e8f9; display: block; font-weight: 600;">Total Fisik di Laci:</span>
                                <span style="font-size: 16px; font-weight: 800; color: #22d3ee; font-family: monospace;">
                                    Rp {{ number_format($prevShiftBilling['drawer_expected_cash'] ?? $prevShiftBilling['total_expected_cash'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                @if(!empty($prevShiftBilling['is_deposited_to_safe']))
                <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; font-size: 11.5px; color: #a7f3d0; display: flex; align-items: center; gap: 8px;">
                    <span>🔒</span>
                    <span><strong>Info SOP Kasir:</strong> Kasir shift sebelumnya telah menyetor uang omset melalui <strong>{{ $prevShiftBilling['deposit_method_label'] ?? 'Brankas' }}</strong>. Anda <strong>hanya perlu menghitung uang modal laci (Rp {{ number_format($prevShiftBilling['drawer_expected_cash'] ?? $prevShiftBilling['total_expected_cash'], 0, ',', '.') }})</strong>.</span>
                </div>
                @endif

                {{-- RINCIAN PECAHAN UANG DARI SHIFT SEBELUMNYA --}}
                @php
                    $denoms = $prevShiftBilling['denominations'] ?? [];
                    $hasDenoms = is_array($denoms) && !empty(array_filter($denoms));
                @endphp
                @if($hasDenoms)
                <div style="background: rgba(0, 0, 0, 0.25); border: 1px dashed rgba(56, 189, 248, 0.3); border-radius: 10px; padding: 10px 14px; margin-bottom: 12px;">
                    <div style="font-size: 10.5px; font-weight: 700; color: #38bdf8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        💵 Rincian Pecahan Kas Shift Sebelumnya:
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px; font-family: monospace;">
                        @if(!empty($denoms['100k']))
                            <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; padding: 2px 7px; color: #bae6fd;">100k: <strong>{{ $denoms['100k'] }}</strong></span>
                        @endif
                        @if(!empty($denoms['50k']))
                            <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; padding: 2px 7px; color: #bae6fd;">50k: <strong>{{ $denoms['50k'] }}</strong></span>
                        @endif
                        @if(!empty($denoms['20k']))
                            <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; padding: 2px 7px; color: #bae6fd;">20k: <strong>{{ $denoms['20k'] }}</strong></span>
                        @endif
                        @if(!empty($denoms['10k']))
                            <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; padding: 2px 7px; color: #bae6fd;">10k: <strong>{{ $denoms['10k'] }}</strong></span>
                        @endif
                        @if(!empty($denoms['5k']))
                            <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; padding: 2px 7px; color: #bae6fd;">5k: <strong>{{ $denoms['5k'] }}</strong></span>
                        @endif
                        @if(!empty($denoms['2k']))
                            <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; padding: 2px 7px; color: #bae6fd;">2k: <strong>{{ $denoms['2k'] }}</strong></span>
                        @endif
                        @if(!empty($denoms['1k']))
                            <span style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 6px; padding: 2px 7px; color: #bae6fd;">1k: <strong>{{ $denoms['1k'] }}</strong></span>
                        @endif
                        @if(!empty($denoms['coins']))
                            <span style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 6px; padding: 2px 7px; color: #fde68a;">Koin: <strong>Rp {{ number_format((float) $denoms['coins'], 0, ',', '.') }}</strong></span>
                        @endif
                    </div>
                </div>
                @endif

                @if(!empty($prevShiftBilling['handover_notes']))
                <div style="font-size: 11px; color: var(--text-muted); background: rgba(0, 0, 0, 0.2); padding: 8px 12px; border-radius: 8px; border-left: 3px solid #06b6d4;">
                    <strong>Catatan Penyerah:</strong> {{ $prevShiftBilling['handover_notes'] }}
                </div>
                @endif
            </div>

            <input type="hidden" name="billing_shift_income" id="billing_shift_income" value="{{ $prevShiftBilling['drawer_expected_cash'] ?? $prevShiftBilling['total_expected_cash'] }}">

            {{-- FORM INPUT PENGHITUNGAN FISIK AKTUAL & CATATAN SELISIH --}}
            <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #e2e8f0; margin-bottom: 0;">
                        Hitung Fisik Uang di Laci Saat Ini:
                    </label>
                    <button type="button" onclick="toggleDenomCalculator()" class="btn" style="padding: 4px 10px; font-size: 11px; font-weight: 700; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                        <span>🧮</span> <span id="toggle-calc-text">Buka Kalkulator Lembaran Uang</span>
                    </button>
                </div>

                {{-- PANEL KALKULATOR PECAHAN LEMBARAN UANG (COLLAPSIBLE) --}}
                <div id="denom-calculator-panel" style="display: none; background: rgba(0, 0, 0, 0.35); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 10px; padding: 12px; margin-bottom: 12px;">
                    <div style="font-size: 11px; font-weight: 700; color: #38bdf8; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                        <span>💵 Hitung Jumlah Lembar / Koin Pecahan Uang:</span>
                        <span id="denom-calc-subtotal" style="font-family: monospace; color: #22d3ee; font-size: 12px; font-weight: 800;">Subtotal: Rp 0</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 8px; font-size: 11px;">
                        <div>
                            <label style="color: #94a3b8; font-size: 10px;">Rp 100.000</label>
                            <input type="number" class="denom-count-input" data-value="100000" min="0" placeholder="0 lbr" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid #334155; border-radius: 6px; padding: 5px 8px; color: #fff; font-family: monospace; font-size: 11.5px; outline: none;" oninput="runDenomCalc()">
                        </div>
                        <div>
                            <label style="color: #94a3b8; font-size: 10px;">Rp 50.000</label>
                            <input type="number" class="denom-count-input" data-value="50000" min="0" placeholder="0 lbr" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid #334155; border-radius: 6px; padding: 5px 8px; color: #fff; font-family: monospace; font-size: 11.5px; outline: none;" oninput="runDenomCalc()">
                        </div>
                        <div>
                            <label style="color: #94a3b8; font-size: 10px;">Rp 20.000</label>
                            <input type="number" class="denom-count-input" data-value="20000" min="0" placeholder="0 lbr" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid #334155; border-radius: 6px; padding: 5px 8px; color: #fff; font-family: monospace; font-size: 11.5px; outline: none;" oninput="runDenomCalc()">
                        </div>
                        <div>
                            <label style="color: #94a3b8; font-size: 10px;">Rp 10.000</label>
                            <input type="number" class="denom-count-input" data-value="10000" min="0" placeholder="0 lbr" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid #334155; border-radius: 6px; padding: 5px 8px; color: #fff; font-family: monospace; font-size: 11.5px; outline: none;" oninput="runDenomCalc()">
                        </div>
                        <div>
                            <label style="color: #94a3b8; font-size: 10px;">Rp 5.000</label>
                            <input type="number" class="denom-count-input" data-value="5000" min="0" placeholder="0 lbr" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid #334155; border-radius: 6px; padding: 5px 8px; color: #fff; font-family: monospace; font-size: 11.5px; outline: none;" oninput="runDenomCalc()">
                        </div>
                        <div>
                            <label style="color: #94a3b8; font-size: 10px;">Rp 2.000</label>
                            <input type="number" class="denom-count-input" data-value="2000" min="0" placeholder="0 lbr" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid #334155; border-radius: 6px; padding: 5px 8px; color: #fff; font-family: monospace; font-size: 11.5px; outline: none;" oninput="runDenomCalc()">
                        </div>
                        <div>
                            <label style="color: #94a3b8; font-size: 10px;">Rp 1.000</label>
                            <input type="number" class="denom-count-input" data-value="1000" min="0" placeholder="0 lbr" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid #334155; border-radius: 6px; padding: 5px 8px; color: #fff; font-family: monospace; font-size: 11.5px; outline: none;" oninput="runDenomCalc()">
                        </div>
                        <div>
                            <label style="color: #94a3b8; font-size: 10px;">Koin / Receh (Rp)</label>
                            <input type="number" id="denom-coins-input" min="0" step="100" placeholder="Rp 0" style="width: 100%; background: rgba(15, 23, 42, 0.9); border: 1px solid #334155; border-radius: 6px; padding: 5px 8px; color: #fbbf24; font-family: monospace; font-size: 11.5px; outline: none;" oninput="runDenomCalc()">
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; margin-bottom: 8px;">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: #e2e8f0; margin-bottom: 4px;">
                            Uang Fisik Diterima Aktual di Laci (Rp) *
                        </label>
                        <div style="position: relative;">
                            <input type="number" name="actual_cash_received" id="actual_cash_received"
                                   value="{{ (int) ($prevShiftBilling['drawer_expected_cash'] ?? $prevShiftBilling['total_expected_cash']) }}"
                                   step="100" min="0" required
                                   oninput="calculateHandoverDiff()"
                                   style="width: 100%; background: rgba(17, 24, 39, 0.85); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 8px; padding: 8px 12px; color: #22d3ee; font-weight: 800; font-family: monospace; font-size: 14px; outline: none;">
                        </div>
                        <div id="handover_diff_badge" style="font-size: 11px; font-weight: 700; margin-top: 4px; color: #34d399;">
                            ✅ Fisik Uang di Laci PAS (Sesuai)
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: #e2e8f0; margin-bottom: 4px;">
                            Catatan Serah Terima / Kondisi Laci:
                            <span id="cash_notes_required_tag" style="display: none; color: #f87171; font-weight: 800;">(WAJIB DIISI KARENA ADA SELISIH)</span>
                        </label>
                        <input type="text" name="cash_handover_notes" id="cash_handover_notes"
                               placeholder="Tulis alasan kronologi jika ada uang selisih/sobek..."
                               style="width: 100%; background: rgba(17, 24, 39, 0.85); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 12px; outline: none;">
                    </div>
                </div>
            </div>

            {{-- VERIFIKASI SALDO KASIR --}}
            <div id="billing_verified_box" style="background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.25); border-radius: 12px; padding: 12px 14px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0;">
                    <input type="checkbox" name="billing_verified" id="billing_verified_checkbox" value="1" checked style="width: 18px; height: 18px; accent-color: #06b6d4; cursor: pointer;">
                    <span id="billing_verified_text" style="font-size: 12px; font-weight: 600; color: #e0f2fe; line-height: 1.45;">
                        Saya telah menghitung fisik uang di laci dan memverifikasi serah terima kas dari shift sebelumnya ({{ $prevShiftBilling['name'] }}) sebesar Rp {{ number_format($prevShiftBilling['drawer_expected_cash'] ?? $prevShiftBilling['total_expected_cash'], 0, ',', '.') }} telah SESUAI.
                    </span>
                </label>
            </div>
        </div>

        <script>
            function toggleDenomCalculator() {
                var p = document.getElementById('denom-calculator-panel');
                var txt = document.getElementById('toggle-calc-text');
                if (!p) return;
                if (p.style.display === 'none') {
                    p.style.display = 'block';
                    if (txt) txt.textContent = 'Tutup Kalkulator Lembaran';
                } else {
                    p.style.display = 'none';
                    if (txt) txt.textContent = 'Buka Kalkulator Lembaran Uang';
                }
            }

            function runDenomCalc() {
                var total = 0;
                document.querySelectorAll('.denom-count-input').forEach(function(inp) {
                    var mult = parseInt(inp.getAttribute('data-value')) || 0;
                    var count = parseInt(inp.value) || 0;
                    total += (mult * count);
                });
                var coinsInput = document.getElementById('denom-coins-input');
                var coins = coinsInput ? (parseInt(coinsInput.value) || 0) : 0;
                total += coins;

                var subtotalEl = document.getElementById('denom-calc-subtotal');
                if (subtotalEl) {
                    subtotalEl.textContent = 'Subtotal: Rp ' + total.toLocaleString('id-ID');
                }
                var actualInput = document.getElementById('actual_cash_received');
                if (actualInput) {
                    actualInput.value = total;
                    calculateHandoverDiff();
                }
            }

            function calculateHandoverDiff() {
                var expected = parseFloat("{{ $prevShiftBilling['drawer_expected_cash'] ?? $prevShiftBilling['total_expected_cash'] }}") || 0;
                var inputEl = document.getElementById('actual_cash_received');
                var badgeEl = document.getElementById('handover_diff_badge');
                var notesTag = document.getElementById('cash_notes_required_tag');
                var notesInput = document.getElementById('cash_handover_notes');
                var verifiedText = document.getElementById('billing_verified_text');
                var verifiedBox = document.getElementById('billing_verified_box');

                var val = parseFloat(inputEl.value) || 0;
                var diff = val - expected;

                if (diff === 0) {
                    badgeEl.style.color = '#34d399';
                    badgeEl.innerHTML = '✅ Fisik Uang di Laci PAS (Sesuai Rp ' + expected.toLocaleString('id-ID') + ')';
                    if (notesTag) notesTag.style.display = 'none';
                    if (notesInput) notesInput.style.borderColor = 'rgba(255, 255, 255, 0.15)';
                    if (verifiedText) {
                        verifiedText.innerHTML = 'Saya telah menghitung fisik uang di laci dan memverifikasi serah terima kas dari shift sebelumnya ({{ $prevShiftBilling['name'] }}) sebesar <strong>Rp ' + expected.toLocaleString('id-ID') + '</strong> telah <strong>SESUAI</strong>.';
                    }
                    if (verifiedBox) {
                        verifiedBox.style.background = 'rgba(6, 182, 212, 0.08)';
                        verifiedBox.style.borderColor = 'rgba(6, 182, 212, 0.25)';
                    }
                } else if (diff > 0) {
                    badgeEl.style.color = '#38bdf8';
                    badgeEl.innerHTML = 'ℹ️ Uang Fisik Lebih: +Rp ' + diff.toLocaleString('id-ID') + ' (Ekspektasi: Rp ' + expected.toLocaleString('id-ID') + ')';
                    if (notesTag) notesTag.style.display = 'inline';
                    if (notesInput) notesInput.style.borderColor = '#38bdf8';
                    if (verifiedText) {
                        verifiedText.innerHTML = 'Saya telah menghitung fisik uang di laci sebesar <strong>Rp ' + val.toLocaleString('id-ID') + '</strong> (Terdapat <strong>Kelebihan Kas +Rp ' + diff.toLocaleString('id-ID') + '</strong>) dan telah mencatat keterangannya untuk diaudit Admin.';
                    }
                    if (verifiedBox) {
                        verifiedBox.style.background = 'rgba(14, 165, 233, 0.12)';
                        verifiedBox.style.borderColor = 'rgba(14, 165, 233, 0.4)';
                    }
                } else {
                    badgeEl.style.color = '#f87171';
                    badgeEl.innerHTML = '⚠️ Uang Fisik Kurang (Selisih): -Rp ' + Math.abs(diff).toLocaleString('id-ID') + ' (Ekspektasi: Rp ' + expected.toLocaleString('id-ID') + ')';
                    if (notesTag) notesTag.style.display = 'inline';
                    if (notesInput) notesInput.style.borderColor = '#f87171';
                    if (verifiedText) {
                        verifiedText.innerHTML = 'Saya telah menghitung fisik uang di laci sebesar <strong>Rp ' + val.toLocaleString('id-ID') + '</strong> (Terdapat <strong>Kekurangan Kas -Rp ' + Math.abs(diff).toLocaleString('id-ID') + '</strong>) dan telah mencatat keterangannya untuk pertanggungjawaban audit Admin.';
                    }
                    if (verifiedBox) {
                        verifiedBox.style.background = 'rgba(239, 68, 68, 0.12)';
                        verifiedBox.style.borderColor = 'rgba(239, 68, 68, 0.4)';
                    }
                }
            }
        </script>

            {{-- 6. TOMBOL SELESAI TAHAP 2 --}}
            <div class="glass-card" style="padding: 22px; text-align: center; border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.05); margin-top: 20px;">
                <div style="margin-bottom: 14px;">
                    <span style="font-size: 14px; font-weight: 800; color: #34d399; display: block; margin-bottom: 4px;">
                        🎉 Seluruh Pemeriksaan (Stok & Kas Laci) Selesai!
                    </span>
                    <p style="font-size: 12px; color: var(--text-muted); max-width: 520px; margin: 0 auto;">
                        Klik tombol di bawah untuk menyimpan seluruh Berita Acara serah terima shift dan langsung membuka <strong>Dashboard POS Kasir Desktop</strong>.
                    </p>
                </div>

                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                    <button type="button" onclick="goToStep1()" class="btn" style="padding: 15px 24px; font-size: 14px; font-weight: 700; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #cbd5e1; border-radius: 8px; cursor: pointer;">
                        ⬅️ Kembali ke Tahap 1 (Cek Stok)
                    </button>
                    <button type="submit" id="btn-submit-stok" class="btn btn-emerald" style="padding: 15px 32px; font-size: 15px; font-weight: 800; min-width: 300px; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); cursor: pointer;">
                        <span>🚀</span> 6. Selesai & Buka POS Kasir Desktop
                    </button>
                </div>

                <div style="margin-top: 14px; font-size: 11px; color: var(--text-sub); display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <span>🔒</span>
                    <span>Untuk <strong>Check Out Presensi Pulang</strong> nanti dilakukan setelah pergantian shift di Halaman Penjualan.</span>
                </div>
            </div>
        </div>
        {{-- AKHIR WADAH TAHAP 2 --}}
    </form>

    {{-- FLOATING TOAST NOTIFIKASI RESTOCK STOK --}}
    <div id="stok-toast" style="display: none; position: fixed; top: 24px; right: 24px; z-index: 9999; padding: 12px 18px; border-radius: 10px; color: #fff; font-size: 13px; font-weight: 600; box-shadow: 0 10px 25px rgba(0,0,0,0.4); max-width: 380px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span id="stok-toast-icon" style="font-size: 18px;">✅</span>
            <div id="stok-toast-msg">Permintaan restock berhasil dikirim!</div>
        </div>
    </div>

    {{-- MODAL AKSI CEPAT RESTOCK STOK --}}
    <div id="modal-quick-restock" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(5px); z-index: 9998; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #0f172a; border: 1px solid rgba(139, 92, 246, 0.4); border-radius: 16px; width: 100%; max-width: 460px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6); overflow: hidden; animation: popIn 0.2s ease-out;">
            {{-- Modal Header --}}
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-glass); display: flex; align-items: center; justify-content: space-between; background: rgba(139, 92, 246, 0.1);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(239, 68, 68, 0.25); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        ⚡
                    </div>
                    <div>
                        <h4 style="font-size: 14.5px; font-weight: 700; color: #fff; margin: 0;">Ajukan Restock Cepat</h4>
                        <span style="font-size: 11px; color: var(--text-muted);">Kirim permintaan barang langsung ke Gudang / Supervisor</span>
                    </div>
                </div>
                <button type="button" onclick="closeQuickRestockModal()" style="background: transparent; border: none; color: var(--text-muted); font-size: 20px; cursor: pointer; padding: 0 4px; line-height: 1;">&times;</button>
            </div>

            {{-- Modal Body --}}
            <form id="form-quick-restock" onsubmit="submitQuickRestock(event)" style="padding: 18px 20px;">
                <input type="hidden" id="qr-category" name="item_category" value="">
                
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Nama Barang:</label>
                    <input type="text" id="qr-name" name="item_name" readonly style="width: 100%; padding: 8px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #38bdf8; font-weight: 700; font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Jumlah Diminta:</label>
                        <input type="number" id="qr-qty" name="qty_requested" min="1" value="1" required style="width: 100%; padding: 8px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-weight: 700; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Satuan:</label>
                        <select id="qr-unit" name="unit" style="width: 100%; padding: 8px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 12.5px;">
                            <option value="Dus">Dus</option>
                            <option value="Rim">Rim</option>
                            <option value="pcs">pcs</option>
                            <option value="Roll">Roll</option>
                            <option value="Pak">Pak</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Tingkat Urgensi:</label>
                    <select id="qr-urgency" name="urgency" style="width: 100%; padding: 8px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 12.5px; font-weight: 600;">
                        <option value="normal">Normal (Bisa menyusul)</option>
                        <option value="high">⚠️ Mendesak (Stok menipis shift ini)</option>
                        <option value="critical">🚨 Kritis / Segera (Habis saat shift berjalan)</option>
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Catatan (Opsional):</label>
                    <input type="text" id="qr-notes" name="notes" placeholder="Catatan untuk logistik..." style="width: 100%; padding: 8px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: 8px; color: #fff; font-size: 12px;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" onclick="closeQuickRestockModal()" class="btn" style="padding: 8px 16px; font-size: 12px; background: rgba(148, 163, 184, 0.15); border: 1px solid rgba(148, 163, 184, 0.3); color: #94a3b8; border-radius: 8px; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-quick-restock" class="btn btn-emerald" style="padding: 8px 18px; font-size: 12px; font-weight: 700; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <span>🚀</span> Kirim Permintaan Restock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openQuickRestockModal(category, name, qty, unit, urgency = 'normal') {
        const modal = document.getElementById('modal-quick-restock');
        if (!modal) return;

        document.getElementById('qr-category').value = category;
        document.getElementById('qr-name').value = name;
        document.getElementById('qr-qty').value = qty || 1;
        document.getElementById('qr-unit').value = unit || 'pcs';
        document.getElementById('qr-urgency').value = urgency || 'normal';
        document.getElementById('qr-notes').value = '';

        modal.style.display = 'flex';
    }

    function closeQuickRestockModal() {
        const modal = document.getElementById('modal-quick-restock');
        if (modal) modal.style.display = 'none';
    }

    function showStokToast(message, isSuccess = true) {
        const toast = document.getElementById('stok-toast');
        if (!toast) return;

        toast.style.display = 'block';
        toast.style.background = isSuccess ? 'rgba(16, 185, 129, 0.95)' : 'rgba(239, 68, 68, 0.95)';
        document.getElementById('stok-toast-icon').innerText = isSuccess ? '✅' : '❌';
        document.getElementById('stok-toast-msg').innerText = message;

        setTimeout(() => {
            toast.style.display = 'none';
        }, 4000);
    }

    async function submitQuickRestock(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-quick-restock');
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.innerHTML = '<span>⏳</span> Mengirim...';
        }

        const payload = {
            item_category: document.getElementById('qr-category').value,
            item_name: document.getElementById('qr-name').value,
            qty_requested: parseInt(document.getElementById('qr-qty').value) || 1,
            unit: document.getElementById('qr-unit').value,
            urgency: document.getElementById('qr-urgency').value,
            notes: document.getElementById('qr-notes').value,
        };

        try {
            const response = await fetch('{{ route("presensi2.penjualan.permintaan-stok") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                closeQuickRestockModal();
                showStokToast(data.message || `Permintaan restock ${payload.item_name} berhasil dikirim!`);
            } else {
                showStokToast(data.message || 'Gagal mengirim permintaan restock.', false);
            }
        } catch (err) {
            console.error('Quick restock error:', err);
            showStokToast('Terjadi kesalahan koneksi saat mengirim permintaan restock.', false);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.innerHTML = '<span>🚀</span> Kirim Permintaan Restock';
            }
        }
    }

    function switchStokAwalTab(tabId, btn) {
        document.querySelectorAll('.stok-awal-tab-pane').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.btn-stok-awal-tab').forEach(b => {
            b.style.background = 'rgba(15, 23, 42, 0.6)';
            b.style.borderColor = 'var(--border-glass)';
            b.style.color = 'var(--text-muted)';
        });

        const target = document.getElementById(tabId);
        if (target) {
            target.style.display = 'block';
        }
        btn.style.background = 'rgba(59, 130, 246, 0.25)';
        btn.style.borderColor = 'rgba(59, 130, 246, 0.5)';
        btn.style.color = '#fff';
    }

    function recalculateStockRow(inputEl) {
        if (!inputEl) return;
        const systemQty = parseInt(inputEl.getAttribute('data-system')) || 0;
        const isCritThreshold = parseInt(inputEl.getAttribute('data-critical-threshold')) || 5;
        const unit = inputEl.getAttribute('data-unit') || 'pcs';
        const targetDiffId = inputEl.getAttribute('data-target-diff');
        const targetStatusId = inputEl.getAttribute('data-target-status');
        const category = inputEl.getAttribute('data-category') || 'minuman';

        let physicalQty = parseInt(inputEl.value);
        if (isNaN(physicalQty) || physicalQty < 0) {
            physicalQty = 0;
        }

        const diff = physicalQty - systemQty;
        const diffEl = document.getElementById(targetDiffId);
        const statusEl = document.getElementById(targetStatusId);

        if (diffEl) {
            if (diff === 0) {
                diffEl.innerHTML = `<span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 10.5px; padding: 2px 7px;">0 ${unit}</span>`;
                inputEl.style.borderColor = 'rgba(59, 130, 246, 0.4)';
                inputEl.style.background = 'rgba(15, 23, 42, 0.8)';
                inputEl.style.color = '#fff';
            } else if (diff < 0) {
                diffEl.innerHTML = `<span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); font-size: 10.5px; padding: 2px 7px; font-weight: 800;">${diff} ${unit}</span>`;
                inputEl.style.borderColor = '#ef4444';
                inputEl.style.background = 'rgba(239, 68, 68, 0.15)';
                inputEl.style.color = '#fca5a5';
            } else {
                diffEl.innerHTML = `<span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.45); font-size: 10.5px; padding: 2px 7px; font-weight: 800;">+${diff} ${unit}</span>`;
                inputEl.style.borderColor = '#38bdf8';
                inputEl.style.background = 'rgba(56, 189, 248, 0.15)';
                inputEl.style.color = '#bae6fd';
            }
        }

        if (statusEl) {
            if (diff < 0) {
                statusEl.innerHTML = `<span class="badge" style="background: rgba(239, 68, 68, 0.25); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.5); font-size: 10px; font-weight: 700;">⚠️ Selisih Kurang</span>`;
            } else if (diff > 0) {
                statusEl.innerHTML = `<span class="badge" style="background: rgba(59, 130, 246, 0.25); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.5); font-size: 10px; font-weight: 700;">ℹ️ Selisih Lebih</span>`;
            } else {
                if (physicalQty <= isCritThreshold) {
                    statusEl.innerHTML = `<span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 10px;">⚠️ Kritis</span>`;
                } else {
                    statusEl.innerHTML = `<span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px;">✓ Aman (Cocok)</span>`;
                }
            }
        }

        updateDiscrepancySummary(category);
        updateOverallStockHandoverSummary();
    }

    function updateOverallStockHandoverSummary() {
        const inputs = document.querySelectorAll('.stock-physical-input');
        let totalItems = inputs.length;
        let matchCount = 0;
        let minusCount = 0;
        let plusCount = 0;

        inputs.forEach(input => {
            const sys = parseInt(input.getAttribute('data-system')) || 0;
            let phys = parseInt(input.value);
            if (isNaN(phys) || phys < 0) phys = 0;
            const diff = phys - sys;
            if (diff === 0) {
                matchCount++;
            } else if (diff < 0) {
                minusCount++;
            } else {
                plusCount++;
            }
        });

        const statTotal = document.getElementById('stat-total-items');
        const statMatch = document.getElementById('stat-match-items');
        const statMinus = document.getElementById('stat-minus-items');
        const statPlus = document.getElementById('stat-plus-items');
        const badgeEl = document.getElementById('stock-handover-status-badge');
        const btnText = document.getElementById('btn-confirm-stock-text');
        const btnIcon = document.getElementById('btn-confirm-stock-icon');
        const btnEl = document.getElementById('btn-confirm-stock-handover');
        const boxEl = document.getElementById('stock-handover-summary-box');

        if (statTotal) statTotal.innerText = totalItems;
        if (statMatch) statMatch.innerText = matchCount;
        if (statMinus) statMinus.innerText = minusCount;
        if (statPlus) statPlus.innerText = plusCount;

        if (badgeEl && btnText && btnEl) {
            if (minusCount > 0) {
                badgeEl.innerHTML = `<span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.45); font-size: 11px; padding: 4px 10px; font-weight: 700;">⚠️ ${minusCount} Item Kurang (Dilaporkan ke Admin)</span>`;
                btnIcon.innerText = '⚠️';
                btnText.innerText = `Laporkan Selisih (${minusCount} Item) & Lanjut ke Cek Hardware ⬇️`;
                btnEl.style.background = 'linear-gradient(135deg, #d97706, #f59e0b)';
                btnEl.style.borderColor = 'rgba(245, 158, 11, 0.6)';
                btnEl.style.boxShadow = '0 4px 12px rgba(245, 158, 11, 0.35)';
                if (boxEl) boxEl.style.borderColor = 'rgba(245, 158, 11, 0.4)';
            } else if (plusCount > 0) {
                badgeEl.innerHTML = `<span class="badge" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.45); font-size: 11px; padding: 4px 10px; font-weight: 700;">ℹ️ ${plusCount} Item Lebih (Sesuai)</span>`;
                btnIcon.innerText = 'ℹ️';
                btnText.innerText = 'Konfirmasi Serah Terima Stok & Lanjut ke Cek Hardware ⬇️';
                btnEl.style.background = 'linear-gradient(135deg, #2563eb, #3b82f6)';
                btnEl.style.borderColor = 'rgba(59, 130, 246, 0.6)';
                btnEl.style.boxShadow = '0 4px 12px rgba(59, 130, 246, 0.35)';
                if (boxEl) boxEl.style.borderColor = 'rgba(59, 130, 246, 0.4)';
            } else {
                badgeEl.innerHTML = `<span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 11px; padding: 4px 10px; font-weight: 700;">✓ Semua Fisik Sesuai Sistem</span>`;
                btnIcon.innerText = '✅';
                btnText.innerText = 'Konfirmasi Serah Terima Stok & Lanjut ke Cek Hardware ⬇️';
                btnEl.style.background = 'linear-gradient(135deg, #059669, #10b981)';
                btnEl.style.borderColor = 'rgba(16, 185, 129, 0.6)';
                btnEl.style.boxShadow = '0 4px 12px rgba(16, 185, 129, 0.35)';
                if (boxEl) boxEl.style.borderColor = 'rgba(16, 185, 129, 0.35)';
            }
        }
    }

    function confirmStockHandoverAndProceed() {
        // 1. Cek apakah ada barang minuman yang kurang tapi belum diberi catatan
        const drinkMinus = Array.from(document.querySelectorAll('.stock-physical-input[data-category="minuman"]')).some(i => {
            const s = parseInt(i.getAttribute('data-system')) || 0;
            const p = parseInt(i.value) || 0;
            return p < s;
        });
        const drinkNotesInput = document.querySelector('input[name="drink_notes"]');
        if (drinkMinus && (!drinkNotesInput || !drinkNotesInput.value.trim())) {
            showIncompleteModal(
                'Catatan Selisih Diperlukan!',
                'Terdapat fisik minuman yang kurang dari sistem. Tuliskan keterangan singkat pada catatan minuman (misal: "Aqua kurang 1 botol dari shift sebelumnya") agar Anda terlindungi dari selisih!'
            );
            switchStokAwalTab('tab-awal-minuman', document.querySelectorAll('.btn-stok-awal-tab')[1]);
            drinkNotesInput?.focus();
            drinkNotesInput?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // 2. Cek apakah ada barang makanan yang kurang tapi belum diberi catatan
        const foodMinus = Array.from(document.querySelectorAll('.stock-physical-input[data-category="makanan"]')).some(i => {
            const s = parseInt(i.getAttribute('data-system')) || 0;
            const p = parseInt(i.value) || 0;
            return p < s;
        });
        const foodNotesInput = document.querySelector('input[name="food_notes"]');
        if (foodMinus && (!foodNotesInput || !foodNotesInput.value.trim())) {
            showIncompleteModal(
                'Catatan Selisih Diperlukan!',
                'Terdapat fisik makanan/snack yang kurang dari sistem. Tuliskan keterangan singkat pada catatan makanan (misal: "Chiki kurang 1 pcs dari shift sebelumnya") agar Anda terlindungi dari selisih!'
            );
            switchStokAwalTab('tab-awal-makanan', document.querySelectorAll('.btn-stok-awal-tab')[2]);
            foodNotesInput?.focus();
            foodNotesInput?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Berikan visual feedback bahwa rekonsiliasi stok sudah dikonfirmasi
        const toast = document.getElementById('stok-toast');
        if (toast) {
            document.getElementById('stok-toast-msg').innerText = 'Pemeriksaan stok fisik telah diverifikasi! Melanjutkan ke kelayakan hardware & kas...';
            document.getElementById('stok-toast-icon').innerText = '📋';
            toast.style.background = '#059669';
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 2500);
        }

        // Scroll mulus ke Seksi 4 (Hardware) atau tombol Lanjut ke Tahap 2
        const nextSection = document.querySelector('select[name="hardware_status"]')?.closest('.glass-card') 
            || document.getElementById('btn-next-to-step2');
        if (nextSection) {
            nextSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            nextSection.style.boxShadow = '0 0 20px rgba(56, 189, 248, 0.4)';
            setTimeout(() => {
                nextSection.style.boxShadow = '';
            }, 1500);
        }
    }

    function goToStep2(validate = false) {
        if (validate) {
            // 1. Cek Catatan jika ada barang minuman yang kurang
            const drinkMinus = Array.from(document.querySelectorAll('.stock-physical-input[data-category="minuman"]')).some(i => {
                const s = parseInt(i.getAttribute('data-system')) || 0;
                const p = parseInt(i.value) || 0;
                return p < s;
            });
            const drinkNotesInput = document.querySelector('input[name="drink_notes"]');
            if (drinkMinus && (!drinkNotesInput || !drinkNotesInput.value.trim())) {
                showIncompleteModal(
                    'Catatan Selisih Minuman Diperlukan!',
                    'Terdapat fisik minuman yang kurang dari sistem. Harap tuliskan kronologi/catatan pada kolom catatan minuman sebelum melanjutkan ke tahap uang laci!'
                );
                goToStep1();
                switchStokAwalTab('tab-awal-minuman', document.querySelectorAll('.btn-stok-awal-tab')[1]);
                drinkNotesInput?.focus();
                drinkNotesInput?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 2. Cek Catatan jika ada barang makanan yang kurang
            const foodMinus = Array.from(document.querySelectorAll('.stock-physical-input[data-category="makanan"]')).some(i => {
                const s = parseInt(i.getAttribute('data-system')) || 0;
                const p = parseInt(i.value) || 0;
                return p < s;
            });
            const foodNotesInput = document.querySelector('input[name="food_notes"]');
            if (foodMinus && (!foodNotesInput || !foodNotesInput.value.trim())) {
                showIncompleteModal(
                    'Catatan Selisih Makanan Diperlukan!',
                    'Terdapat fisik makanan/snack yang kurang dari sistem. Harap tuliskan kronologi/catatan pada kolom catatan makanan sebelum melanjutkan ke tahap uang laci!'
                );
                goToStep1();
                switchStokAwalTab('tab-awal-makanan', document.querySelectorAll('.btn-stok-awal-tab')[2]);
                foodNotesInput?.focus();
                foodNotesInput?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 3. Cek Checklist Hardware
            const hardwareChecked = document.querySelectorAll('input[name="hardware_checklist[]"]:checked');
            if (hardwareChecked.length === 0) {
                showIncompleteModal(
                    'Pemeriksaan Hardware Belum Lengkap!',
                    'Unit hardware PC station belum ada yang diverifikasi. Harap periksa dan centang unit hardware sebelum melanjutkan ke kas laci.'
                );
                goToStep1();
                const hwSection = document.querySelector('select[name="hardware_status"]')?.closest('.glass-card');
                if (hwSection) {
                    hwSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }

            // 4. Cek Catatan Hardware jika ada kendala
            const hwStatus = document.querySelector('select[name="hardware_status"]')?.value;
            const hwNotes = document.querySelector('input[name="hardware_notes"]');
            if (hwStatus && hwStatus !== 'normal' && (!hwNotes || !hwNotes.value.trim())) {
                showIncompleteModal(
                    'Catatan Kendala Hardware Diperlukan!',
                    'Anda memilih status kendala pada hardware. Harap isi catatan nomor PC atau kendala yang ditemukan sebelum melanjutkan ke kas laci.'
                );
                goToStep1();
                hwNotes?.focus();
                hwNotes?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        }

        // Tampilkan Tahap 2, sembunyikan Tahap 1
        const s1 = document.getElementById('step-1-container');
        const s2 = document.getElementById('step-2-container');
        if (s1) s1.style.display = 'none';
        if (s2) s2.style.display = 'block';

        // Update Navigasi Tab Wizard
        const tab1 = document.getElementById('step-tab-1');
        const tab1Icon = document.getElementById('step-tab-1-icon');
        const tab2 = document.getElementById('step-tab-2');
        const tab2Icon = document.getElementById('step-tab-2-icon');
        const tab2Title = document.getElementById('step-tab-2-title');
        const tab2Sub = document.getElementById('step-tab-2-sub');

        if (tab1) {
            tab1.style.background = 'rgba(16, 185, 129, 0.12)';
            tab1.style.borderColor = '#10b981';
        }
        if (tab1Icon) {
            tab1Icon.style.background = '#059669';
            tab1Icon.innerHTML = '✓';
        }
        if (tab2) {
            tab2.style.background = 'rgba(6, 182, 212, 0.18)';
            tab2.style.borderColor = '#06b6d4';
        }
        if (tab2Icon) {
            tab2Icon.style.background = '#06b6d4';
            tab2Icon.style.color = '#fff';
        }
        if (tab2Title) tab2Title.style.color = '#f8fafc';
        if (tab2Sub) tab2Sub.style.color = '#bae6fd';

        calculateHandoverDiff();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return true;
    }

    function goToStep1() {
        const s1 = document.getElementById('step-1-container');
        const s2 = document.getElementById('step-2-container');
        if (s2) s2.style.display = 'none';
        if (s1) s1.style.display = 'block';

        const tab1 = document.getElementById('step-tab-1');
        const tab1Icon = document.getElementById('step-tab-1-icon');
        const tab2 = document.getElementById('step-tab-2');
        const tab2Icon = document.getElementById('step-tab-2-icon');
        const tab2Title = document.getElementById('step-tab-2-title');
        const tab2Sub = document.getElementById('step-tab-2-sub');

        if (tab1) {
            tab1.style.background = 'rgba(14, 165, 233, 0.18)';
            tab1.style.borderColor = '#0284c7';
        }
        if (tab1Icon) {
            tab1Icon.style.background = '#0284c7';
            tab1Icon.innerHTML = '1';
        }
        if (tab2) {
            tab2.style.background = 'rgba(255, 255, 255, 0.04)';
            tab2.style.borderColor = 'rgba(255, 255, 255, 0.1)';
        }
        if (tab2Icon) {
            tab2Icon.style.background = 'rgba(255, 255, 255, 0.12)';
            tab2Icon.style.color = '#94a3b8';
        }
        if (tab2Title) tab2Title.style.color = '#94a3b8';
        if (tab2Sub) tab2Sub.style.color = '#64748b';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function updateDiscrepancySummary(category) {
        const inputs = document.querySelectorAll(`.stock-physical-input[data-category="${category}"]`);
        let minusCount = 0;
        let plusCount = 0;

        inputs.forEach(input => {
            const sys = parseInt(input.getAttribute('data-system')) || 0;
            let phys = parseInt(input.value);
            if (isNaN(phys) || phys < 0) phys = 0;
            const diff = phys - sys;
            if (diff < 0) {
                minusCount++;
            } else if (diff > 0) {
                plusCount++;
            }
        });

        const banner = document.getElementById(`${category}-discrepancy-banner`);
        if (banner) {
            const totalDiff = minusCount + plusCount;
            if (totalDiff > 0) {
                banner.style.display = 'block';
                banner.innerHTML = `<strong>⚠️ Perhatian Selisih Fisik vs Sistem:</strong> Ditemukan <strong>${totalDiff} item</strong> berselisih (${minusCount > 0 ? minusCount + ' kurang' : ''}${minusCount > 0 && plusCount > 0 ? ', ' : ''}${plusCount > 0 ? plusCount + ' lebih' : ''}). Mohon tuliskan alasan/kronologi pada catatan di bawah agar kasir shift ini terlindungi dari pemotongan ganti rugi!`;
            } else {
                banner.style.display = 'none';
            }
        }
    }

    function applyStockPreset(presetType) {
        const inputs = document.querySelectorAll('.stock-physical-input');

        if (presetType === 'match') {
            inputs.forEach(input => {
                const sys = parseInt(input.getAttribute('data-system')) || 0;
                input.value = sys;
                recalculateStockRow(input);
            });
            const drinkNotes = document.querySelector('input[name="drink_notes"]');
            if (drinkNotes) drinkNotes.value = 'Simulasi: Seluruh stok fisik minuman telah diperiksa dan cocok dengan sistem.';
            const foodNotes = document.querySelector('input[name="food_notes"]');
            if (foodNotes) foodNotes.value = 'Simulasi: Seluruh stok fisik makanan telah diperiksa dan cocok.';
            showStokToast('Preset "Semua Cocok" berhasil diterapkan! Seluruh barang selisih 0.');
        } else if (presetType === 'shortage') {
            let drinkShortageApplied = false;
            let foodShortageApplied = false;

            inputs.forEach(input => {
                const sys = parseInt(input.getAttribute('data-system')) || 0;
                const category = input.getAttribute('data-category');

                if (category === 'minuman' && !drinkShortageApplied) {
                    input.value = Math.max(0, sys - 1);
                    drinkShortageApplied = true;
                } else if (category === 'makanan' && !foodShortageApplied) {
                    input.value = Math.max(0, sys - 1);
                    foodShortageApplied = true;
                } else {
                    input.value = sys;
                }
                recalculateStockRow(input);
            });

            const drinkNotes = document.querySelector('input[name="drink_notes"]');
            if (drinkNotes) drinkNotes.value = 'Simulasi: Ditemukan kekurangan 1 botol Aqua dari shift sebelumnya saat serah terima.';
            const foodNotes = document.querySelector('input[name="food_notes"]');
            if (foodNotes) foodNotes.value = 'Simulasi: Ditemukan kekurangan 1 bungkus makanan dari shift sebelumnya.';
            showStokToast('Preset "Simulasi Selisih Kurang" aktif! Aqua / makanan diset kurang 1 pcs untuk diuji di Admin.');
        } else if (presetType === 'critical') {
            inputs.forEach(input => {
                input.value = 3;
                recalculateStockRow(input);
            });
            showStokToast('Preset "Stok Menipis" aktif! Kuantitas diset 3 pcs untuk menguji tombol Restock Cepat.');
        }

        ['kertas', 'minuman', 'makanan'].forEach(cat => updateDiscrepancySummary(cat));
        updateOverallStockHandoverSummary();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi perhitungan selisih stok awal secara realtime
        document.querySelectorAll('.stock-physical-input').forEach(input => {
            recalculateStockRow(input);
        });
        ['kertas', 'minuman', 'makanan'].forEach(cat => updateDiscrepancySummary(cat));
        updateOverallStockHandoverSummary();

        // Buka langsung Tahap 2 jika diminta via parameter URL ?step=2
        if (new URLSearchParams(window.location.search).get('step') === '2') {
            goToStep2();
        }

        const stokForm = document.querySelector('form[action="{{ route('presensi2.stok.store') }}"]');
        if (!stokForm) return;

        stokForm.addEventListener('submit', function(e) {
            // 1. Cek Catatan jika ada barang minuman yang kurang
            const drinkMinus = Array.from(document.querySelectorAll('.stock-physical-input[data-category="minuman"]')).some(i => {
                const s = parseInt(i.getAttribute('data-system')) || 0;
                const p = parseInt(i.value) || 0;
                return p < s;
            });
            const drinkNotesVal = document.querySelector('input[name="drink_notes"]')?.value.trim();
            if (drinkMinus && !drinkNotesVal) {
                e.preventDefault();
                showIncompleteModal(
                    'Perhatian Selisih Minuman!',
                    'Terdapat stok fisik minuman yang kurang dari catatan sistem. Harap tuliskan kronologi/catatan pada kolom catatan minuman (misal: Aqua kurang 1 dari shift sebelumnya) agar kasir shift ini aman dari pembebanan selisih kasir!'
                );
                document.querySelector('input[name="drink_notes"]')?.focus();
                document.querySelector('input[name="drink_notes"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 2. Cek Catatan jika ada barang makanan yang kurang
            const foodMinus = Array.from(document.querySelectorAll('.stock-physical-input[data-category="makanan"]')).some(i => {
                const s = parseInt(i.getAttribute('data-system')) || 0;
                const p = parseInt(i.value) || 0;
                return p < s;
            });
            const foodNotesVal = document.querySelector('input[name="food_notes"]')?.value.trim();
            if (foodMinus && !foodNotesVal) {
                e.preventDefault();
                showIncompleteModal(
                    'Perhatian Selisih Makanan!',
                    'Terdapat stok fisik makanan/snack yang kurang dari catatan sistem. Harap tuliskan kronologi/catatan pada kolom catatan makanan (misal: barang rusak atau kurang dari shift sebelumnya) agar terlindungi dari pemotongan ganti rugi!'
                );
                document.querySelector('input[name="food_notes"]')?.focus();
                document.querySelector('input[name="food_notes"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 3. Cek Verifikasi Kasir Shift Sebelumnya
            const billingCheckbox = document.querySelector('input[name="billing_verified"]');
            if (billingCheckbox && !billingCheckbox.checked) {
                e.preventDefault();
                showIncompleteModal(
                    'Pemeriksaan Belum Lengkap!',
                    'Anda belum mencentang verifikasi serah terima kas billing dari shift sebelumnya. Harap periksa dan centang verifikasi kas terlebih dahulu.'
                );
                billingCheckbox.closest('div').style.boxShadow = '0 0 15px rgba(239, 68, 68, 0.6)';
                billingCheckbox.closest('div').style.borderColor = 'rgba(239, 68, 68, 0.8)';
                billingCheckbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 4. Cek Checklist Hardware
            const hardwareChecked = document.querySelectorAll('input[name="hardware_checklist[]"]:checked');
            if (hardwareChecked.length === 0) {
                e.preventDefault();
                showIncompleteModal(
                    'Pemeriksaan Belum Lengkap!',
                    'Item checklist kelayakan hardware belum ada yang dicentang. Harap periksa dan centang unit hardware yang beroperasi.'
                );
                const hwSection = document.querySelector('select[name="hardware_status"]').closest('.glass-card');
                if (hwSection) {
                    hwSection.style.boxShadow = '0 0 15px rgba(239, 68, 68, 0.6)';
                    hwSection.style.borderColor = 'rgba(239, 68, 68, 0.8)';
                    hwSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }

            // 5. Cek Catatan Hardware jika ada kendala
            const hwStatus = document.querySelector('select[name="hardware_status"]')?.value;
            const hwNotes = document.querySelector('input[name="hardware_notes"]');
            if (hwStatus && hwStatus !== 'normal' && (!hwNotes || !hwNotes.value.trim())) {
                e.preventDefault();
                showIncompleteModal(
                    'Pemeriksaan Belum Lengkap!',
                    'Anda memilih status kendala pada hardware. Mohon isi catatan kendala hardware yang ditemukan agar tim teknisi dapat menindaklanjuti.'
                );
                hwNotes?.focus();
                hwNotes?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 6. Cek Catatan Selisih Uang Kas Fisik di Laci
            const expectedCash = parseFloat("{{ $prevShiftBilling['drawer_expected_cash'] ?? $prevShiftBilling['total_expected_cash'] }}") || 0;
            const actualCashInput = document.getElementById('actual_cash_received');
            const actualCash = actualCashInput ? (parseFloat(actualCashInput.value) || 0) : expectedCash;
            const cashDiff = actualCash - expectedCash;
            const cashNotes = document.getElementById('cash_handover_notes')?.value.trim();

            if (Math.abs(cashDiff) > 0.001 && !cashNotes) {
                e.preventDefault();
                const diffStr = (cashDiff < 0 ? '-Rp ' : '+Rp ') + Math.abs(cashDiff).toLocaleString('id-ID');
                showIncompleteModal(
                    'Perhatian Selisih Kas Laci!',
                    'Terdapat selisih antara uang fisik di laci dengan ekspektasi sistem (' + diffStr + '). Harap isi Catatan Serah Terima / Kondisi Laci (misal: uang receh kurang atau ada uang sobek) agar Anda terlindungi dari tuduhan selisih kasir!'
                );
                document.getElementById('cash_handover_notes')?.focus();
                document.getElementById('cash_handover_notes')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        });
    });
</script>
@endpush
