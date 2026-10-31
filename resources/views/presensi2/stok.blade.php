@extends('layouts.presensi2')

@section('title', 'Pemeriksaan Stok Barang & Serah Terima Shift')

@section('content')
<div class="stok-page">
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

    <form action="{{ route('presensi2.stok.store') }}" method="POST">
        @csrf

        {{-- 1, 2, & 3. KARTU TERPADU BER-TAB: STOK KERTAS, MINUMAN, & MAKANAN --}}
        <div class="glass-card" style="padding: 20px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        📦
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">1–3. Pemeriksaan Stok Kertas, Minuman & Makanan</h3>
                        <span style="font-size: 11px; color: var(--text-muted);">Pilih tab kategori di samping untuk memeriksa stok awal vs pemakaian/penjualan hari ini</span>
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

            {{-- TAB 1: STOK KERTAS PRINT --}}
            <div id="tab-awal-kertas" class="stok-awal-tab-pane">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-glass); color: var(--text-muted);">
                                <th style="padding: 8px 10px;">Jenis Kertas</th>
                                <th style="padding: 8px 10px; text-align: center;">Stok Shift Sebelumnya</th>
                                <th style="padding: 8px 10px; text-align: center;">Penjualan / Terpakai Hari Ini</th>
                                <th style="padding: 8px 10px; text-align: center;">Sisa Stok Saat Ini</th>
                                <th style="padding: 8px 10px; text-align: right;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockComparisons['kertas'] ?? [] as $k)
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="padding: 10px; color: #fff; font-weight: 600;">
                                    {{ $k['name'] }}
                                </td>
                                <td style="padding: 10px; text-align: center; color: #93c5fd; font-weight: 700;">
                                    {{ $k['sebelumnya'] }} <span style="font-size: 10px; color: var(--text-muted);">{{ $k['unit'] }}</span>
                                </td>
                                <td style="padding: 10px; text-align: center; color: #fbbf24; font-weight: 700;">
                                    {{ $k['terjual'] > 0 ? '-'.$k['terjual'] : '0' }} <span style="font-size: 10px; color: var(--text-muted);">{{ $k['unit'] }}</span>
                                </td>
                                <td style="padding: 10px; text-align: center; color: #34d399; font-weight: 700;">
                                    {{ $k['sesudahnya'] }} <span style="font-size: 10px; color: var(--text-muted);">{{ $k['unit'] }}</span>
                                </td>
                                <td style="padding: 10px; text-align: right;">
                                    <span class="badge badge-present" style="font-size: 10px;">{{ $k['rim_info'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <input type="hidden" name="paper_a4" value="1">
                <input type="hidden" name="paper_a4_qty" value="{{ $stockComparisons['kertas'][0]['sebelumnya'] ?? 500 }}">
                <input type="hidden" name="paper_f4" value="1">
                <input type="hidden" name="paper_f4_qty" value="{{ $stockComparisons['kertas'][1]['sebelumnya'] ?? 500 }}">
            </div>

            {{-- TAB 2: STOK MINUMAN --}}
            <div id="tab-awal-minuman" class="stok-awal-tab-pane" style="display: none;">
                <div style="overflow-x: auto; max-height: 300px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-glass); color: var(--text-muted);">
                                <th style="padding: 8px 10px;">Nama Minuman</th>
                                <th style="padding: 8px 10px; text-align: center;">Stok Shift Sebelumnya</th>
                                <th style="padding: 8px 10px; text-align: center;">Penjualan Hari Ini</th>
                                <th style="padding: 8px 10px; text-align: center;">Sisa Stok Saat Ini</th>
                                <th style="padding: 8px 10px; text-align: right;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockComparisons['minuman'] ?? [] as $d)
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="padding: 9px 10px; color: #fff; font-weight: 600;">
                                    {{ $d['name'] }}
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: #93c5fd; font-weight: 700;">
                                    {{ $d['sebelumnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: #fbbf24; font-weight: 700;">
                                    {{ $d['terjual'] > 0 ? '-'.$d['terjual'] : '0' }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: #34d399; font-weight: 700;">
                                    {{ $d['sesudahnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 9px 10px; text-align: right;">
                                    @if($d['sesudahnya'] <= 5)
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; font-size: 10px;">Menipis</span>
                                    @else
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px;">Aman</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 10px;">
                    <input type="text" name="drink_notes" value="{{ old('drink_notes', $existingChecklist->drink_notes ?? '') }}" placeholder="Catatan tambahan minuman (misal: sirup habis, es batu cukup)..." style="width: 100%; padding: 9px 12px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 10px; color: #fff; font-size: 12px;">
                </div>
            </div>

            {{-- TAB 3: STOK MAKANAN & SNACK --}}
            <div id="tab-awal-makanan" class="stok-awal-tab-pane" style="display: none;">
                <div style="overflow-x: auto; max-height: 300px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border-glass); color: var(--text-muted);">
                                <th style="padding: 8px 10px;">Nama Makanan / Snack</th>
                                <th style="padding: 8px 10px; text-align: center;">Stok Shift Sebelumnya</th>
                                <th style="padding: 8px 10px; text-align: center;">Penjualan Hari Ini</th>
                                <th style="padding: 8px 10px; text-align: center;">Sisa Stok Saat Ini</th>
                                <th style="padding: 8px 10px; text-align: right;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockComparisons['makanan'] ?? [] as $m)
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <td style="padding: 9px 10px; color: #fff; font-weight: 600;">
                                    {{ $m['name'] }}
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: #93c5fd; font-weight: 700;">
                                    {{ $m['sebelumnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: #fbbf24; font-weight: 700;">
                                    {{ $m['terjual'] > 0 ? '-'.$m['terjual'] : '0' }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 9px 10px; text-align: center; color: #34d399; font-weight: 700;">
                                    {{ $m['sesudahnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                                </td>
                                <td style="padding: 9px 10px; text-align: right;">
                                    @if($m['sesudahnya'] <= 5)
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; font-size: 10px;">Menipis</span>
                                    @else
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px;">Aman</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 10px;">
                    <input type="text" name="food_notes" value="{{ old('food_notes', $existingChecklist->food_notes ?? '') }}" placeholder="Catatan tambahan makanan (misal: telur sisa 10 butir, kornet aman)..." style="width: 100%; padding: 9px 12px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 10px; color: #fff; font-size: 12px;">
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
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #38bdf8; font-weight: 700;">
                            ⏮️ Shift Sebelumnya yang Menyerahkan Kas:
                        </span>
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
                                Rp {{ number_format($prevShiftBilling['total_expected_cash'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

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

            <input type="hidden" name="billing_shift_income" id="billing_shift_income" value="{{ $prevShiftBilling['total_expected_cash'] }}">

            {{-- FORM INPUT PENGHITUNGAN FISIK AKTUAL & CATATAN SELISIH --}}
            <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px; margin-bottom: 12px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 11.5px; font-weight: 700; color: #e2e8f0; margin-bottom: 4px;">
                            Uang Fisik Diterima Aktual di Laci (Rp):
                        </label>
                        <div style="position: relative;">
                            <input type="number" name="actual_cash_received" id="actual_cash_received"
                                   value="{{ $prevShiftBilling['total_expected_cash'] }}"
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
                        </label>
                        <input type="text" name="cash_handover_notes" id="cash_handover_notes"
                               placeholder="Tulis catatan jika ada selisih uang atau kondisi uang rusak..."
                               style="width: 100%; background: rgba(17, 24, 39, 0.85); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 8px 12px; color: #fff; font-size: 12px; outline: none;">
                    </div>
                </div>
            </div>

            {{-- VERIFIKASI SALDO KASIR --}}
            <div style="background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.25); border-radius: 12px; padding: 12px 14px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0;">
                    <input type="checkbox" name="billing_verified" value="1" checked style="width: 18px; height: 18px; accent-color: #06b6d4; cursor: pointer;">
                    <span style="font-size: 12px; font-weight: 600; color: #e0f2fe;">
                        Saya telah menghitung fisik uang di laci dan memverifikasi serah terima kas dari shift sebelumnya ({{ $prevShiftBilling['name'] }}) sebesar Rp {{ number_format($prevShiftBilling['total_expected_cash'], 0, ',', '.') }} telah sesuai.
                    </span>
                </label>
            </div>
        </div>

        <script>
            function calculateHandoverDiff() {
                var expected = parseFloat("{{ $prevShiftBilling['total_expected_cash'] }}") || 0;
                var inputEl = document.getElementById('actual_cash_received');
                var badgeEl = document.getElementById('handover_diff_badge');
                var val = parseFloat(inputEl.value) || 0;
                var diff = val - expected;

                if (diff === 0) {
                    badgeEl.style.color = '#34d399';
                    badgeEl.innerHTML = '✅ Fisik Uang di Laci PAS (Sesuai)';
                } else if (diff > 0) {
                    badgeEl.style.color = '#38bdf8';
                    badgeEl.innerHTML = 'ℹ️ Uang Fisik Lebih: +Rp ' + diff.toLocaleString('id-ID');
                } else {
                    badgeEl.style.color = '#f87171';
                    badgeEl.innerHTML = '⚠️ Uang Fisik Kurang (Selisih): -Rp ' + Math.abs(diff).toLocaleString('id-ID');
                }
            }
        </script>

        {{-- 6. TOMBOL SELESAI --}}
        <div class="glass-card" style="padding: 22px; text-align: center; border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.05);">
            <div style="margin-bottom: 12px;">
                <span style="font-size: 13px; font-weight: 700; color: #34d399; display: block; margin-bottom: 4px;">
                    🎉 Pemeriksaan Selesai!
                </span>
                <p style="font-size: 12px; color: var(--text-muted); max-width: 520px; margin: 0 auto;">
                    Klik tombol di bawah untuk menyimpan checklist stok dan Anda akan langsung diarahkan ke <strong>Halaman Penjualan</strong>.
                </p>
            </div>

            <button type="submit" id="btn-submit-stok" class="btn btn-emerald" style="padding: 16px 28px; font-size: 16px; font-weight: 700; width: 100%; max-width: 480px; margin: 0 auto; box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);">
                <span>🚀</span> 6. Selesai & Lanjut ke Halaman Penjualan
            </button>

            <div style="margin-top: 14px; font-size: 11px; color: var(--text-sub); display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span>🔒</span>
                <span>Untuk <strong>Check Out Presensi</strong> nanti dilakukan setelah pergantian shift di Halaman Penjualan.</span>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
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

    document.addEventListener('DOMContentLoaded', function() {
        const stokForm = document.querySelector('form[action="{{ route('presensi2.stok.store') }}"]');
        if (!stokForm) return;

        stokForm.addEventListener('submit', function(e) {
            // 1. Cek Verifikasi Kasir Shift Sebelumnya
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

            // 2. Cek Checklist Hardware
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

            // 3. Cek Catatan Hardware jika ada kendala
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
        });
    });
</script>
@endpush
