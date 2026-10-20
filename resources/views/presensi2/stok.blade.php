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

    {{-- PANDUAN RINGKAS --}}
    <div class="glass-card" style="background: rgba(14, 165, 233, 0.08); border-color: rgba(14, 165, 233, 0.25); padding: 14px 18px; margin-bottom: 18px;">
        <div style="display: flex; gap: 12px; align-items: flex-start;">
            <span style="font-size: 20px; line-height: 1;">📝</span>
            <div style="font-size: 12px; color: #bae6fd; line-height: 1.5;">
                <strong>Instruksi Operasional:</strong> Pastikan Anda telah memeriksa ketersediaan fisik <strong>kertas (A4 & F4)</strong>, stok <strong>minuman & makanan</strong>, kelayakan <strong>hardware</strong>, serta mencocokkan <strong>pendapatan billing shift sebelumnya</strong>. Klik <strong>Selesai</strong> untuk masuk ke <strong>Halaman Penjualan</strong>.
            </div>
        </div>
    </div>

    <form action="{{ route('presensi2.stok.store') }}" method="POST">
        @csrf

        {{-- 1.STOK KERTAS PRINT --}}
        <div class="glass-card" style="padding: 20px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        📄
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">1. Stok Kertas Print</h3>
                        <span style="font-size: 11px; color: var(--text-muted);">Tabel informasi perbandingan stok shift sebelumnya vs pemakaian hari ini</span>
                    </div>
                </div>
                <span class="badge" style="background: rgba(59, 130, 246, 0.15); color: #93c5fd;">Tabel Informasi</span>
            </div>

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

        {{-- 2 STOK MINUMAN --}}
        <div class="glass-card" style="padding: 20px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        🥤
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">2. Stok Minuman</h3>
                        <span style="font-size: 11px; color: var(--text-muted);">Tabel informasi perbandingan stok shift sebelumnya vs penjualan hari ini</span>
                    </div>
                </div>
                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399;">Showcase Kulkas</span>
            </div>

            <div style="overflow-x: auto;">
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
                            <td style="padding: 10px; color: #fff; font-weight: 600;">
                                {{ $d['name'] }}
                            </td>
                            <td style="padding: 10px; text-align: center; color: #93c5fd; font-weight: 700;">
                                {{ $d['sebelumnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                            </td>
                            <td style="padding: 10px; text-align: center; color: #fbbf24; font-weight: 700;">
                                {{ $d['terjual'] > 0 ? '-'.$d['terjual'] : '0' }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                            </td>
                            <td style="padding: 10px; text-align: center; color: #34d399; font-weight: 700;">
                                {{ $d['sesudahnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                            </td>
                            <td style="padding: 10px; text-align: right;">
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

            <div style="margin-top: 12px;">
                <input type="text" name="drink_notes" value="{{ old('drink_notes', $existingChecklist->drink_notes ?? '') }}" placeholder="Catatan tambahan minuman (misal: sirup habis, es batu cukup)..." style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 10px; color: #fff; font-size: 12px;">
            </div>
        </div>

        {{-- 3. STOK MAKANAN & SNACK --}}
        <div class="glass-card" style="padding: 20px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        🍜
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">3. Stok Makanan & Snack</h3>
                        <span style="font-size: 11px; color: var(--text-muted);">Tabel informasi perbandingan stok shift sebelumnya vs penjualan hari ini</span>
                    </div>
                </div>
                <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">Etalase Kasir</span>
            </div>

            <div style="overflow-x: auto;">
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
                            <td style="padding: 10px; color: #fff; font-weight: 600;">
                                {{ $m['name'] }}
                            </td>
                            <td style="padding: 10px; text-align: center; color: #93c5fd; font-weight: 700;">
                                {{ $m['sebelumnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                            </td>
                            <td style="padding: 10px; text-align: center; color: #fbbf24; font-weight: 700;">
                                {{ $m['terjual'] > 0 ? '-'.$m['terjual'] : '0' }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                            </td>
                            <td style="padding: 10px; text-align: center; color: #34d399; font-weight: 700;">
                                {{ $m['sesudahnya'] }} <span style="font-size: 10px; color: var(--text-muted);">pcs</span>
                            </td>
                            <td style="padding: 10px; text-align: right;">
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

            <div style="margin-top: 12px;">
                <input type="text" name="food_notes" value="{{ old('food_notes', $existingChecklist->food_notes ?? '') }}" placeholder="Catatan tambahan makanan (misal: telur sisa 10 butir, kornet aman)..." style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-glass); border-radius: 10px; color: #fff; font-size: 12px;">
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

        {{-- 5. CEK DAFTAR BILLING INTERNET SHIFT SEBELUMNYA --}}
        <div class="glass-card" style="padding: 20px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-glass); padding-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(6, 182, 212, 0.18); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        💰
                    </div>
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">5. Cek Daftar Billing Internet Shift Sebelumnya</h3>
                        <span style="font-size: 11px; color: var(--text-muted);">Verifikasi pendapatan kas & serah terima billing dari operator shift sebelumnya</span>
                    </div>
                </div>
                <span class="badge" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee; font-size: 11px; padding: 4px 10px;">
                    Serah Terima Kas Masuk
                </span>
            </div>

            {{-- KARTU KHUSUS SHIFT SEBELUMNYA --}}
            <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(6, 182, 212, 0.35); border-radius: 14px; padding: 18px; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
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
                            Operator: <strong style="color: #c4b5fd;">{{ $prevShiftBilling['operator_name'] }}</strong>
                        </div>
                    </div>
                    <div style="text-align: right; background: rgba(0, 0, 0, 0.3); padding: 10px 16px; border-radius: 12px; border: 1px solid rgba(6, 182, 212, 0.2);">
                        <span style="font-size: 11px; color: var(--text-muted); display: block;">Pendapatan Billing Shift Sebelumnya:</span>
                        <div style="font-size: 22px; font-weight: 800; color: #22d3ee; font-family: 'Outfit', monospace; margin-top: 2px;">
                            Rp {{ number_format($prevShiftBilling['amount'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                @if(!empty($prevShiftBilling['handover_notes']))
                <div style="font-size: 11px; color: var(--text-muted); background: rgba(0, 0, 0, 0.2); padding: 8px 12px; border-radius: 8px; border-left: 3px solid #06b6d4;">
                    <strong>Catatan Shift Sebelumnya:</strong> {{ $prevShiftBilling['handover_notes'] }}
                </div>
                @endif
            </div>

            <input type="hidden" name="billing_shift_income" value="{{ $prevShiftBilling['amount'] }}">

            {{-- VERIFIKASI SALDO KASIR --}}
            <div style="background: rgba(6, 182, 212, 0.08); border: 1px solid rgba(6, 182, 212, 0.25); border-radius: 12px; padding: 12px 14px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0;">
                    <input type="checkbox" name="billing_verified" value="1" checked style="width: 18px; height: 18px; accent-color: #06b6d4; cursor: pointer;">
                    <span style="font-size: 12px; font-weight: 600; color: #e0f2fe;">
                        Saya telah memverifikasi saldo laci kasir dan serah terima kas billing dari shift sebelumnya ({{ $prevShiftBilling['name'] }}) sebesar Rp {{ number_format($prevShiftBilling['amount'], 0, ',', '.') }} telah sesuai.
                    </span>
                </label>
            </div>
        </div>

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
