@extends('layouts.presensi2')

@section('title', 'AMBIL STOK — Portal Restock & Logistik MGK')

@section('content')
<div class="ambil-stok-portal-page">
    {{-- TOP NAVIGATION BAR / BACK BUTTON --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
        <a href="{{ route('presensi2.layanan.login', ['service' => 'ambil-stok']) }}" class="btn-back-link" style="display: inline-flex; align-items: center; gap: 8px; color: #94a3b8; font-size: 13px; font-weight: 600; text-decoration: none; padding: 8px 14px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: 12px; transition: all 0.2s ease;">
            <span>&larr;</span>
            <span>Kembali ke Portal Layanan</span>
        </a>

        @if(session()->has('presensi2_employee_id'))
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ route('presensi2.stok') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #c4b5fd; font-size: 12px; font-weight: 600; text-decoration: none;">
                    <span>📦</span>
                    <span>Checklist Stok</span>
                </a>
                <a href="{{ route('presensi2.index') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #38bdf8; font-size: 12px; font-weight: 600; text-decoration: none;">
                    <span>🏠</span>
                    <span>Beranda</span>
                </a>
            </div>
        @endif
    </div>

    {{-- HEADER OUTLET & STATUS --}}
    <div class="glass-card" style="padding: 16px 18px; margin-bottom: 14px; border-color: rgba(168, 85, 247, 0.25);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted);">
                    Logistik & Rantai Pasok Outlet
                </span>
                <h2 style="font-size: 18px; font-weight: 800; color: #fff; margin-top: 2px; display: flex; align-items: center; gap: 8px;">
                    <span>📦</span> AMBIL STOK
                </h2>
            </div>
            <div style="text-align: right;">
                <span class="badge" style="background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35); font-size: 11px; padding: 5px 12px;">
                    📦 Restock & Penerimaan
                </span>
            </div>
        </div>

        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--border-glass); display: flex; align-items: center; justify-content: space-between; font-size: 12px; flex-wrap: wrap; gap: 6px;">
            <span style="color: var(--text-sub);">Loker Kasir / Cabang:</span>
            <strong style="color: #e5e7eb; display: inline-flex; align-items: center; gap: 6px;">
                <span>📍</span> {{ $currentEmployee->branch->name ?? 'Maya Graha Kencana Pusat' }}
            </strong>
        </div>
    </div>

    {{-- LIVE DIGITAL CLOCK (SAMA SEPERTI HALAMAN PRESENSI) --}}
    <div class="glass-card" style="text-align: center; padding: 24px 18px; margin-bottom: 16px; background: radial-gradient(circle at 50% 50%, rgba(168, 85, 247, 0.08) 0%, rgba(17, 24, 39, 0.9) 100%);">
        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 6px;">
            Waktu Operasional Ambil Stok (WIB)
        </div>
        <div id="live-clock" style="font-size: 46px; font-weight: 800; font-family: 'Outfit', monospace; color: #fff; letter-spacing: 2px; text-shadow: 0 0 24px rgba(168, 85, 247, 0.45);">
            {{ now()->format('H:i:s') }}
        </div>
        <div style="font-size: 13px; color: var(--text-sub); margin-top: 4px; font-weight: 500;">
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    {{-- STATS SUMMARY ROW --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px;">
        <div class="glass-card" style="padding: 12px; text-align: center; margin-bottom: 0;">
            <div style="font-size: 20px; font-weight: 800; color: #c084fc; font-family: 'Outfit';">{{ $stats['total_requests'] }}</div>
            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Ambil Stok Hari Ini</div>
        </div>
        <div class="glass-card" style="padding: 12px; text-align: center; margin-bottom: 0;">
            <div style="font-size: 20px; font-weight: 800; color: #fbbf24; font-family: 'Outfit';">{{ $stats['pending_transfers'] }}</div>
            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Pengiriman Masuk</div>
        </div>
        <div class="glass-card" style="padding: 12px; text-align: center; margin-bottom: 0;">
            <div style="font-size: 20px; font-weight: 800; color: #34d399; font-family: 'Outfit';">{{ $stats['received_transfers'] }}</div>
            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Batch Diterima</div>
        </div>
    </div>

    {{-- KARTU IDENTITAS KARYAWAN TERVERIFIKASI (TIDAK BISA ASAL PILIH NAMA) --}}
    <div class="glass-card" style="padding: 14px 16px; margin-bottom: 16px; border-color: rgba(168, 85, 247, 0.3); background: rgba(15, 23, 42, 0.85);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #7e22ce, #a855f7); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);">
                    👤
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                        Karyawan Terautentikasi:
                    </span>
                    <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-top: 1px;">
                        {{ $currentEmployee->name }}
                        <span style="font-size: 11px; font-weight: normal; color: #c084fc;">({{ $currentEmployee->code ?? 'Karyawan' }})</span>
                    </h3>
                    <div style="font-size: 11px; color: var(--text-sub);">
                        {{ $currentEmployee->position ?? 'Staf Operasional' }} &bull; 📍 {{ $currentEmployee->branch->name ?? 'Cabang MGK' }}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; font-size: 11px; padding: 4px 10px;">
                    🔒 Terverifikasi Login
                </span>
                <a href="{{ route('presensi2.layanan.logout', ['service' => 'ambil-stok']) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 10px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fca5a5; font-size: 11px; font-weight: 600; text-decoration: none;">
                    <span>🚪</span> Ganti Akun
                </a>
            </div>
        </div>
    </div>

    {{-- FORMULIR AMBIL STOK / PERMINTAAN RESTOCK --}}
    <div class="glass-card" style="padding: 22px 18px; margin-bottom: 18px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(168, 85, 247, 0.18); color: #c084fc; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                📦
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff;">Formulir Ambil Stok & Permintaan Restock</h3>
                <p style="font-size: 12px; color: var(--text-muted);">Masukkan Password / PIN konfirmasi dan detail barang yang diambil.</p>
            </div>
        </div>

        <form action="{{ route('presensi2.ambil-stok.store') }}" method="POST" id="form-ambil-stok">
            @csrf

            {{-- VERIFIKASI PASSWORD / PIN KEAMANAN --}}
            <div style="margin-bottom: 18px; padding: 14px 16px; border-radius: 14px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(168, 85, 247, 0.35);">
                <label for="credential" class="form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="display: flex; align-items: center; gap: 6px; color: #fff;">
                        <span>🔐</span> Password atau PIN 6-Digit Anda:
                    </span>
                    <span style="font-size: 11px; color: #c084fc; font-weight: normal;">(Wajib untuk Validasi)</span>
                </label>
                <div style="position: relative;">
                    <input type="password" name="credential" id="credential" class="form-input" placeholder="Masukkan Password atau PIN 6-Digit Akun Anda" required minlength="4" autocomplete="current-password" style="padding-left: 42px;" autofocus>
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;">🔑</span>
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                    Konfirmasi kredensial keamanan untuk memvalidasi permintaan ambil stok atas nama Anda.
                </div>
            </div>

            {{-- 3. KATEGORI ITEM --}}
            <div style="margin-bottom: 16px;">
                <label for="item_category" class="form-label">📂 Kategori Item:</label>
                <div style="position: relative;">
                    <select name="item_category" id="item_category" class="form-input" required onchange="handleCategoryChange(this.value)">
                        <option value="Minuman">🥤 Minuman Cafe / Dingin</option>
                        <option value="Makanan & Snack">🍟 Makanan & Snack</option>
                        <option value="Kertas & ATK" selected>📄 Kertas Struk / A4 / F4</option>
                        <option value="Hardware & Aksesoris">🖱️ Hardware PC, Mouse & Keyboard</option>
                        <option value="Kebersihan & Sanitasi">🧹 Perlengkapan Sanitasi / Toko</option>
                    </select>
                    <div style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted); font-size: 12px;">▼</div>
                </div>
            </div>

            {{-- 4. NAMA BARANG --}}
            <div style="margin-bottom: 16px;">
                <label for="item_name" class="form-label">📦 Nama Barang yang Diambil:</label>
                <input type="text" name="item_name" id="item_name" class="form-input" placeholder="Contoh: Kertas Print A4 70gr atau Le Minerale 600ml" required value="Kertas Print A4 70gr (1 Rim)">

                {{-- QUICK SELECT CHIPS --}}
                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px;">
                    <button type="button" class="btn-chip" onclick="setStokItem('Kertas Print A4 70gr', 'Rim', 'Kertas & ATK')">📄 Kertas A4 (Rim)</button>
                    <button type="button" class="btn-chip" onclick="setStokItem('Kertas Print F4 Folio 70gr', 'Rim', 'Kertas & ATK')">📑 Kertas F4 (Rim)</button>
                    <button type="button" class="btn-chip" onclick="setStokItem('Kertas Thermal Struk Kasir 80mm', 'Roll', 'Kertas & ATK')">🧾 Thermal Kasir</button>
                    <button type="button" class="btn-chip" onclick="setStokItem('Air Mineral Le Minerale 600ml', 'Botol', 'Minuman')">💧 Le Minerale</button>
                    <button type="button" class="btn-chip" onclick="setStokItem('Teh Pucuk Harum 350ml', 'Botol', 'Minuman')">🍵 Teh Pucuk</button>
                    <button type="button" class="btn-chip" onclick="setStokItem('Indomie Goreng Original', 'Pcs', 'Makanan & Snack')">🍜 Indomie Goreng</button>
                    <button type="button" class="btn-chip" onclick="setStokItem('Mouse Gaming USB Standar', 'Unit', 'Hardware & Aksesoris')">🖱️ Mouse PC</button>
                </div>
            </div>

            {{-- 5. JUMLAH (QTY) & SATUAN --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label for="qty_requested" class="form-label">🔢 Jumlah (Qty):</label>
                    <input type="number" name="qty_requested" id="qty_requested" class="form-input" min="1" max="500" value="1" required>
                </div>
                <div>
                    <label for="unit" class="form-label">📏 Satuan:</label>
                    <div style="position: relative;">
                        <select name="unit" id="unit" class="form-input" required>
                            <option value="Rim">Rim</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Botol">Botol</option>
                            <option value="Box">Box / Dus</option>
                            <option value="Roll">Roll</option>
                            <option value="Unit">Unit</option>
                        </select>
                        <div style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted); font-size: 12px;">▼</div>
                    </div>
                </div>
            </div>

            {{-- 6. TINGKAT URGENSI --}}
            <div style="margin-bottom: 16px;">
                <label for="urgency" class="form-label">⚠️ Tingkat Kebutuhan / Urgensi:</label>
                <div style="position: relative;">
                    <select name="urgency" id="urgency" class="form-input">
                        <option value="normal">Normal — Persediaan shift berjalan</option>
                        <option value="high">Mendesak — Stok di loker kasir hampir habis</option>
                        <option value="critical">Kritis — Stok fisik kosong / pelanggan menunggu</option>
                    </select>
                    <div style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted); font-size: 12px;">▼</div>
                </div>
            </div>

            {{-- 7. CATATAN --}}
            <div style="margin-bottom: 20px;">
                <label for="notes" class="form-label">📝 Keterangan / Alasan Ambil Stok (Opsional):</label>
                <textarea name="notes" id="notes" rows="2" class="form-input" placeholder="Tuliskan tujuan penggunaan stok atau nomor loker kasir..."></textarea>
            </div>

            {{-- SUBMIT BUTTON --}}
            <button type="submit" class="btn" style="padding: 16px; font-size: 15px; font-weight: 700; width: 100%; border-radius: 14px; background: linear-gradient(135deg, #7c3aed, #a855f7); color: #fff; box-shadow: 0 8px 24px rgba(168, 85, 247, 0.4);">
                <span>📦</span> Konfirmasi Ambil Stok & Ajukan Restock
            </button>
        </form>
    </div>

    {{-- BAGIAN PENERIMAAN PENGIRIMAN STOK (DISTRIBUSI GUDANG PUSAT) --}}
    <div class="glass-card" style="padding: 18px 16px; margin-bottom: 18px; border-color: rgba(245, 158, 11, 0.25);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <span>🚚</span> Penerimaan Pengiriman Stok dari Gudang Pusat
            </h4>
            <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">
                {{ $incomingTransfers->count() }} Pengiriman
            </span>
        </div>

        @if($incomingTransfers->isEmpty())
            <div style="text-align: center; padding: 20px 10px; color: var(--text-muted); font-size: 12px;">
                Belum ada pengiriman stok yang dijadwalkan untuk cabang ini.
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($incomingTransfers as $tf)
                    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-glass); border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div style="flex: 1; min-width: 180px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-family: monospace; font-size: 12px; font-weight: 700; color: #38bdf8;">{{ $tf->transfer_code }}</span>
                                @if($tf->status === 'received')
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px; padding: 2px 8px;">✅ Diterima</span>
                                @else
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; font-size: 10px; padding: 2px 8px;">🚚 Dalam Pengiriman</span>
                                @endif
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: #fff; margin-top: 3px;">
                                {{ $tf->variant_name ?? 'Item Produk' }}
                                <span style="color: #34d399; margin-left: 6px;">+{{ $tf->qty }} pcs</span>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                Ke: {{ $tf->to_warehouse_name ?? 'Loker Kasir' }} &bull; {{ $tf->dispatched_at ? \Carbon\Carbon::parse($tf->dispatched_at)->format('d M H:i') : '-' }}
                            </div>
                        </div>

                        <div>
                            @if($tf->status !== 'received')
                                <form action="{{ route('presensi2.ambil-stok.terima', $tf->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-emerald" style="padding: 8px 14px; font-size: 12px; width: auto;" onclick="return confirm('Konfirmasi bahwa barang fisik {{ $tf->variant_name }} (+{{ $tf->qty }} pcs) telah sampai dan diterima di outlet?');">
                                        <span>✅</span> Konfirmasi Terima
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 11px; color: var(--text-muted);">
                                    ✓ Diverifikasi
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- RIWAYAT PERMINTAAN AMBIL STOK HARI INI --}}
    <div class="glass-card" style="padding: 18px 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <span>📋</span> Riwayat Permintaan Ambil Stok Hari Ini ({{ now()->format('d/m/Y') }})
            </h4>
            <span class="badge" style="background: rgba(168, 85, 247, 0.15); color: #c084fc;">
                {{ $stockRequests->count() }} Permohonan
            </span>
        </div>

        @if($stockRequests->isEmpty())
            <div style="text-align: center; padding: 24px 10px; color: var(--text-muted); font-size: 13px;">
                <div style="font-size: 32px; margin-bottom: 6px;">📦</div>
                Belum ada catatan pengambilan atau permintaan stok hari ini.
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($stockRequests as $req)
                    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-glass); border-radius: 12px; padding: 12px 14px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                            <strong style="color: #fff; font-size: 13px;">{{ $req->employee->name ?? 'Kasir / Staf' }}</strong>
                            <span class="badge" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; font-size: 10px; padding: 2px 8px;">
                                {{ $req->request_number }}
                            </span>
                        </div>
                        <div style="font-size: 13px; color: #c084fc; font-weight: 700; margin-bottom: 4px;">
                            {{ $req->item_name }} &bull; <span style="color: #34d399;">{{ $req->qty_requested }} {{ $req->unit }}</span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: var(--text-muted);">
                            <span>📂 {{ $req->item_category }} &bull; 📍 {{ $req->branch->name ?? 'MGK' }}</span>
                            <span>⏱️ {{ $req->created_at->format('H:i') }} WIB</span>
                        </div>
                        @if($req->notes)
                            <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed var(--border-glass); font-size: 11px; color: var(--text-sub); font-style: italic;">
                                💬 {{ $req->notes }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<style>
    .btn-chip {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid var(--border-glass);
        color: #cbd5e1;
        font-size: 11px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-chip:hover {
        background: rgba(168, 85, 247, 0.15);
        border-color: rgba(168, 85, 247, 0.4);
        color: #c084fc;
    }
</style>

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const el = document.getElementById('live-clock');
        if (el) el.textContent = `${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateClock, 1000);

    function setStokItem(name, unit, category) {
        const nameInput = document.getElementById('item_name');
        const unitSelect = document.getElementById('unit');
        const categorySelect = document.getElementById('item_category');
        if (nameInput) nameInput.value = name;
        if (unitSelect) unitSelect.value = unit;
        if (categorySelect && category) categorySelect.value = category;
    }
</script>
@endpush
@endsection
