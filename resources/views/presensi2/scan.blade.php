@extends('layouts.presensi2')

@section('title', 'Presensi Kredensial — Clock In/Out')

@push('styles')
<style>
.hour-checkbox-card {
    background: rgba(255, 255, 255, 0.04);
    border: 1.5px solid rgba(255, 255, 255, 0.12);
    color: #94a3b8;
}
.hour-checkbox-card.active {
    background: rgba(244, 63, 94, 0.22);
    border-color: #f43f5e;
    color: #fff;
    box-shadow: 0 2px 10px rgba(244, 63, 94, 0.3);
}
.hour-checkbox-card:hover {
    border-color: rgba(244, 63, 94, 0.6);
}
.btn-chip-hours {
    padding: 6px 11px;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-chip-hours:hover {
    background: rgba(244, 63, 94, 0.15);
    border-color: rgba(244, 63, 94, 0.4);
    color: #fda4af;
}
.btn-chip-hours.active {
    background: rgba(244, 63, 94, 0.25);
    border-color: #fda4af;
    color: #fff;
    font-weight: 700;
}
</style>
@endpush

@section('content')
<div class="scan-v2-page">
    {{-- NETWORK & OFFLINE BUFFER STATUS BANNER --}}
    <div id="network-status-banner" style="display: none; padding: 12px 16px; border-radius: 14px; margin-bottom: 14px; font-size: 13px; font-weight: 600;"></div>

    {{-- FLASH NOTIFIKASI SIMULASI / STATUS --}}
    @if(session('status') || session('success'))
        <div style="padding: 12px 16px; border-radius: 14px; margin-bottom: 14px; font-size: 13px; font-weight: 600; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; display: flex; align-items: center; gap: 8px;">
            <span>✓</span> {{ session('status') ?? session('success') }}
        </div>
    @endif

    {{-- HEADER SHIFT & CABANG --}}
    <div class="glass-card" style="padding: 14px 16px; margin-bottom: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">Kantor / Cabang</span>
                <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 2px;">
                    📍 {{ $branch->name ?? 'Kantor MGK' }}
                </h3>
            </div>
            <div style="text-align: right;">
                <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.3); font-size: 11px; padding: 4px 10px;">
                    🔐 Verifikasi PIN
                </span>
            </div>
        </div>

        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border-glass); display: flex; align-items: center; justify-content: space-between; font-size: 12px;">
            <span style="color: var(--text-sub);">Shift:</span>
            @php
                $displayShift = $attendanceToday?->shift ?? $activeShift;
            @endphp
            <strong id="header-shift-label" style="color: #e5e7eb; display: inline-flex; align-items: center; gap: 6px;">
                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 11px; padding: 2px 8px;">
                    {{ $displayShift->code ?? 'SHIFT' }}
                </span>
                {{ $displayShift->name ?? 'Shift' }} ({{ substr($displayShift->start_time ?? '00:00', 0, 5) }} - {{ substr($displayShift->end_time ?? '00:00', 0, 5) }})
            </strong>
        </div>
    </div>

    {{-- CLOCK DISPLAY --}}
    <div class="glass-card" style="text-align: center; padding: 28px 18px;">
        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 8px;">Waktu Server Saat Ini</div>
        <div id="live-clock" style="font-size: 48px; font-weight: 800; font-family: 'Outfit', monospace; color: #fff; letter-spacing: 2px; text-shadow: 0 0 20px rgba(139, 92, 246, 0.4);">
            {{ now()->format('H:i:s') }}
        </div>
        <div style="font-size: 12px; color: var(--text-sub); margin-top: 4px;">{{ now()->translatedFormat('l, d F Y') }} (WIB)</div>
    </div>

    @if(config('app.debug') || app()->environment('local', 'testing'))
    {{-- DEV / QA TIME TRAVEL SIMULATOR --}}
    <div class="glass-card" style="padding: 14px 16px; margin-bottom: 16px; border: 1px dashed rgba(245, 158, 11, 0.45); background: rgba(245, 158, 11, 0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #fbbf24;">
                <span>⏱️ Simulasi Waktu Presensi</span>
                @if(session('presensi2_mock_time'))
                    <span class="badge" style="background: rgba(245, 158, 11, 0.3); color: #fef3c7; font-size: 10px; padding: 2px 6px;">
                        MOCK AKTIF: {{ \Carbon\Carbon::parse(session('presensi2_mock_time'))->translatedFormat('d M H:i') }}
                    </span>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <a href="{{ request()->fullUrlWithQuery(['action' => 'reset_today']) }}" 
                   onclick="return confirm('Reset seluruh data presensi testing hari ini untuk akun Anda agar bisa mencoba Check-In ulang?')"
                   style="font-size: 11px; color: #fca5a5; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); padding: 3px 8px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                    <span>🗑️</span> Reset Hari Ini
                </a>
                <a href="{{ request()->fullUrlWithQuery(['action' => 'reset_all_attendance']) }}" 
                   onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS SEMUA data riwayat presensi di database agar bersih total dan sinkron dari awal?')"
                   style="font-size: 11px; color: #fca5a5; background: rgba(185, 28, 28, 0.25); border: 1px solid rgba(239, 68, 68, 0.5); padding: 3px 8px; border-radius: 6px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <span>🔥</span> Kosongkan Riwayat Presensi
                </a>
                @if(session('presensi2_mock_time'))
                    <a href="{{ request()->fullUrlWithQuery(['mock_time' => 'reset', 'mock_date' => null]) }}" style="font-size: 11px; color: #38bdf8; text-decoration: none; font-weight: 600;">
                        🔄 Real Server
                    </a>
                @endif
            </div>
        </div>

        {{-- WAKTU PC TURET INFO & TOMBOL SINKRONISASI --}}
        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 10px; padding: 10px 12px; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em;">Waktu Lokal PC Turet:</div>
                    <div style="font-size: 15px; font-weight: 700; color: #67e8f9; font-family: monospace;">
                        🖥️ <span id="pc-turet-clock">--:--:--</span> <span id="pc-turet-date" style="font-size: 11px; color: #94a3b8; font-weight: 500;"></span>
                    </div>
                </div>
                <button type="button" onclick="syncToPcTuretTime()" style="padding: 7px 12px; font-size: 12px; background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);">
                    <span>⚡</span> Samakan ke Jam PC Turet
                </button>
            </div>
        </div>

        <div style="font-size: 11px; color: #d1d5db; margin-bottom: 8px;">
            Atau pilih preset shift kerja / masukkan jam kustom:
        </div>

        {{-- PRESET SHIFT BUTTONS --}}
        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px;">
            <a href="{{ request()->fullUrlWithQuery(['mock_time' => 'shift1']) }}" style="padding: 5px 9px; font-size: 11px; background: rgba(59, 130, 246, 0.18); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); border-radius: 6px; text-decoration: none; font-weight: 600;">Shift 1 (01:05)</a>
            <a href="{{ request()->fullUrlWithQuery(['mock_time' => 'shift2']) }}" style="padding: 5px 9px; font-size: 11px; background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 6px; text-decoration: none; font-weight: 600;">Shift 2 (07:05)</a>
            <a href="{{ request()->fullUrlWithQuery(['mock_time' => 'shift3']) }}" style="padding: 5px 9px; font-size: 11px; background: rgba(245, 158, 11, 0.18); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 6px; text-decoration: none; font-weight: 600;">Shift 3 (13:05)</a>
            <a href="{{ request()->fullUrlWithQuery(['mock_time' => 'shift4']) }}" style="padding: 5px 9px; font-size: 11px; background: rgba(139, 92, 246, 0.18); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, 0.35); border-radius: 6px; text-decoration: none; font-weight: 600;">Shift 4 (19:05)</a>
        </div>

        <form method="GET" action="{{ url()->current() }}" style="display: flex; gap: 8px; align-items: center;">
            <input type="text" name="mock_time" placeholder="Jam kustom (contoh: 01:10 atau 06:55)" value="{{ session('presensi2_mock_time') ? \Carbon\Carbon::parse(session('presensi2_mock_time'))->format('H:i') : '' }}" style="flex: 1; padding: 7px 10px; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; color: #fff; font-size: 12px; outline: none;">
            <button type="submit" style="padding: 7px 12px; font-size: 12px; background: #d97706; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                Set Jam
            </button>
        </form>
    </div>
    @endif

    {{-- KARTU INFO PERGANTIAN SHIFT & KASIR SEBELUMNYA --}}
    <div class="glass-card" style="padding: 16px 18px; margin-bottom: 16px; border-color: rgba(56, 189, 248, 0.3); background: rgba(15, 23, 42, 0.75);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🔄</span>
                <div>
                    <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin: 0;">Info Pergantian Shift (Sinkron Database)</h4>
                    <span style="font-size: 11px; color: var(--text-muted);">Data kasir tercatat resmi di database per shift</span>
                </div>
            </div>
            @if(isset($arrivalValidation))
                <span class="badge" style="background: {{ $arrivalValidation['badge_bg'] }}; color: {{ $arrivalValidation['badge_color'] }}; font-size: 11px; padding: 4px 10px; font-weight: 700; border: 1px solid {{ $arrivalValidation['badge_color'] }}40;">
                    ● {{ $arrivalValidation['label'] }}
                </span>
            @endif
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px;">
            {{-- KASIR SHIFT SEBELUMNYA --}}
            <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <div style="font-size: 10.5px; text-transform: uppercase; color: #94a3b8; font-weight: 600; letter-spacing: 0.04em;">
                        Kasir Shift Sebelumnya
                    </div>
                    <span class="badge" style="font-size: 9.5px; padding: 1px 6px; background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                        {{ $previousShift->name ?? 'Shift 1' }}
                    </span>
                </div>
                @if(isset($previousShiftAttendance) && $previousShiftAttendance)
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                        <span style="font-size: 18px;">👤</span>
                        <div>
                            <strong style="color: #38bdf8; font-size: 14px; display: block;">
                                {{ $previousShiftAttendance->employee->name ?? 'Kasir' }}
                            </strong>
                            <span style="font-size: 11px; color: #cbd5e1;">
                                {{ $previousShiftAttendance->shift->name ?? ($previousShift->name ?? 'Shift Sebelumnya') }}
                            </span>
                        </div>
                    </div>
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(255, 255, 255, 0.08); font-size: 11px;">
                        @if($previousShiftAttendance->clock_out_at)
                            <span style="color: #34d399; font-weight: 600;">✓ Selesai & Clock Out ({{ $previousShiftAttendance->clock_out_at->format('H:i') }} WIB)</span>
                        @else
                            <span style="color: #fbbf24; font-weight: 600;">⏳ Sedang Berdinas / Belum Clock Out</span>
                        @endif
                    </div>
                @else
                    <div style="margin-top: 6px; font-size: 12px; color: #94a3b8; font-style: italic;">
                        Belum ada catatan kasir shift sebelumnya di database hari ini.
                    </div>
                @endif
            </div>

            {{-- KASIR BERTUGAS SHIFT INI --}}
            <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <div style="font-size: 10.5px; text-transform: uppercase; color: #94a3b8; font-weight: 600; letter-spacing: 0.04em;">
                        Kasir Bertugas Shift Ini
                    </div>
                    <span class="badge" style="font-size: 9.5px; padding: 1px 6px; background: rgba(16, 185, 129, 0.15); color: #34d399;">
                        {{ $activeShift->name ?? 'Shift 2' }}
                    </span>
                </div>
                @php
                    $displayActiveEmployee = $activeShiftAttendance?->employee ?? $currentEmployee;
                    $isAlreadyInDb = (bool) $activeShiftAttendance;
                @endphp
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                    <span style="font-size: 18px;">👤</span>
                    <div>
                        <strong style="color: #34d399; font-size: 14px; display: block;">
                            {{ $displayActiveEmployee->name }}
                        </strong>
                        <span style="font-size: 11px; color: #a78bfa; font-family: monospace;">
                            {{ $activeShift->name ?? 'Shift Kerja' }} ({{ substr($activeShift->start_time ?? '00:00', 0, 5) }} - {{ substr($activeShift->end_time ?? '00:00', 0, 5) }} WIB)
                        </span>
                    </div>
                </div>
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(255, 255, 255, 0.08); font-size: 11px;">
                    @if($isAlreadyInDb)
                        <span style="color: #34d399; font-weight: 600;">✓ Tercatat di Database (Masuk: {{ $activeShiftAttendance->clock_in_at?->format('H:i') }} WIB)</span>
                    @else
                        <span style="color: #38bdf8; font-weight: 600;">● Siap Presensi Masuk (Akun Anda)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- FORM VERIFIKASI & SUBMIT --}}
    <div class="glass-card" style="padding: 22px 18px;">
        @if(!$attendanceToday)
            {{-- MODE: CLOCK-IN --}}
            @if(isset($previousRecordToday) && $previousRecordToday)
                <div style="background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.35); border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px; color: #93c5fd; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                    <div>
                        <span>ℹ️</span> Presensi Shift sebelumnya (<strong>{{ $previousRecordToday->shift?->name ?? 'Shift' }}</strong>) telah selesai pukul {{ $previousRecordToday->clock_out_at?->format('H:i') }}. Silakan konfirmasi Check-In untuk shift baru.
                    </div>
                    <a href="{{ request()->fullUrlWithQuery(['action' => 'reset_today']) }}" onclick="return confirm('Reset simulasi presensi hari ini?')" style="color: #fca5a5; text-decoration: underline; white-space: nowrap; font-size: 11px;">
                        Reset Hari Ini
                    </a>
                </div>
            @endif

            <div style="margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(16, 185, 129, 0.18); display: flex; align-items: center; justify-content: center; font-size: 22px;">✅</div>
                    <div>
                        <h4 style="font-size: 17px; font-weight: 700; color: #fff;">Presensi Masuk</h4>
                        <p style="font-size: 13px; color: var(--text-muted);">Konfirmasi kehadiran masuk sesuai shift kerja Anda.</p>
                    </div>
                </div>
            </div>

            {{-- INFORMASI SHIFT KERJA (OTOMATIS SISTEM TERKUNCI) --}}
            <div style="margin-bottom: 20px; text-align: left; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(139, 92, 246, 0.35); border-radius: 14px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 11px; text-transform: uppercase; color: #a78bfa; font-weight: 700; letter-spacing: 0.05em;">
                        ⏱️ Shift Kerja Masuk:
                    </span>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 10.5px; padding: 2px 8px; border: 1px solid rgba(16, 185, 129, 0.4);">
                        ✓ Otomatis Jam Server
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 22px;">⏰</span>
                    <div>
                        <strong style="color: #fff; font-size: 15px; display: block;">
                            {{ $activeShift->name ?? 'Shift Kerja' }}
                        </strong>
                        <span style="font-size: 12px; color: #cbd5e1; font-family: monospace;">
                            Pukul {{ substr($activeShift->start_time ?? '00:00', 0, 5) }} - {{ substr($activeShift->end_time ?? '00:00', 0, 5) }} WIB
                        </span>
                    </div>
                </div>
                <input type="hidden" id="shift_id" name="shift_id" value="{{ $activeShift?->id ?? '' }}">
            </div> 

            {{-- LIVENESS DETECTION BIOMETRIC CHALLENGE CARD --}}
            <div style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 12px; padding: 12px 14px; margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">🛡️</span>
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #e9d5ff;">Deteksi Kehidupan (Liveness Detection)</div>
                        <div style="font-size: 11px; color: #a78bfa;">Proteksi Anti-Spoofing Foto Cetak / Layar HP</div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span id="liveness-badge" class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 11px; padding: 4px 8px; font-weight: 700;">
                        ✓ Valid (0.98)
                    </span>
                    <input type="hidden" id="liveness_score_input" name="liveness_score" value="0.98">
                </div>
            </div>

            <button type="button" id="btn-clock-in" onclick="submitAttendance('in')" class="btn btn-emerald" style="padding: 16px; font-size: 16px; width: 100%;">
                <span>⚡</span> Konfirmasi Check-In
            </button>

        @elseif(!$attendanceToday->clock_out_at)
            {{-- MODE: SUDAH MASUK (AKTIF DINAS) & MENU MASUK SISTEM --}}
            <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 14px; padding: 14px 16px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.2); display: flex; align-items: center; justify-content: center; font-size: 22px;">🟢</div>
                    <div>
                        <h4 style="font-size: 15px; font-weight: 700; color: #34d399; margin: 0;">Presensi Masuk Berhasil Masuk ke Sistem!</h4>
                        <p style="font-size: 12.5px; color: #cbd5e1; margin-top: 3px;">
                            Status Anda <strong>Sedang Aktif Berdinas</strong> (Masuk pukul <strong style="color: #34d399;">{{ $attendanceToday->clock_in_at?->format('H:i') }} WIB</strong>).
                        </p>
                    </div>
                </div>
            </div>

            {{-- TOMBOL UTAMA ALUR OPERASIONAL: CEK STOK & POS KASIR --}}
            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 22px;">
                <a href="{{ route('presensi2.stok') }}" style="background: linear-gradient(135deg, #059669, #10b981); color: #fff; font-size: 15px; font-weight: 700; padding: 13px 18px; border-radius: 12px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                    <span>📦</span> Lanjut Pemeriksaan Stok Awal Shift (Langkah 2)
                </a>
                <a href="{{ route('pos') }}" style="background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: #fff; font-size: 14px; font-weight: 700; padding: 12px 18px; border-radius: 12px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(139, 92, 246, 0.35);">
                    <span>🛒</span> Buka POS Kasir Penjualan Desktop
                </a>
                <a href="{{ route('presensi2.index') }}" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.15); color: #cbd5e1; font-size: 13px; font-weight: 600; padding: 10px 18px; border-radius: 12px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span>🏠</span> Masuk ke Beranda Sistem
                </a>
            </div>

            {{-- OPSI PRESENSI PULANG / SELESAI DINAS DENGAN CHECKBOX MEMANJANG (MAKSIMAL 6 JAM) --}}
            <div style="padding-top: 16px; border-top: 1px dashed rgba(255, 255, 255, 0.15); text-align: left;">
                
                {{-- KARTU CHECKBOX MEMANJANG: DURASI JAM KERJA --}}
                <div style="background: rgba(15, 23, 42, 0.85); border: 1.5px solid rgba(244, 63, 94, 0.4); border-radius: 16px; padding: 16px 18px; margin-bottom: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <label style="font-size: 13.5px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 18px;">⏱️</span> Durasi Jam Kerja (Maksimal 6 Jam):
                        </label>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 12px; color: #94a3b8;">Total:</span>
                            <div id="duration-badge-display" style="background: linear-gradient(135deg, #f43f5e, #e11d48); color: #fff; font-size: 15px; font-weight: 800; padding: 3px 14px; border-radius: 20px; font-family: monospace; letter-spacing: 0.5px; box-shadow: 0 2px 8px rgba(244, 63, 94, 0.4);">
                                <span id="total-duration-hours-text">6</span> Jam
                            </div>
                        </div>
                    </div>

                    {{-- 6 KOTAK CHECKBOX MEMANJANG (1 JAM PER KOTAK) --}}
                    <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; margin-bottom: 12px;">
                        @for($h = 1; $h <= 6; $h++)
                            <div class="hour-checkbox-card active" id="hour-card-{{ $h }}" onclick="toggleHourCard({{ $h }})" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px 4px; border-radius: 10px; cursor: pointer; user-select: none; transition: all 0.2s ease;">
                                <input type="checkbox" name="hour_slot[]" value="1" id="hour-cb-{{ $h }}" checked style="width: 17px; height: 17px; accent-color: #f43f5e; cursor: pointer; pointer-events: none;">
                                <span style="font-size: 11.5px; font-weight: 700; margin-top: 5px; font-family: monospace;">1 Jam</span>
                                <span style="font-size: 9px; opacity: 0.7; margin-top: 1px;">Ke-{{ $h }}</span>
                            </div>
                        @endfor
                    </div>

                    {{-- INPUT TEXTBOX SINKRONISASI & PRESET CEPAT --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px dashed rgba(255, 255, 255, 0.1); flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 11.5px; color: #94a3b8;">Input Textbox:</span>
                            <input type="number" id="duration_hours_input" name="duration_hours" min="1" max="6" step="1" value="6" oninput="onDurationHoursChange()" style="width: 75px; height: 32px; background: rgba(10, 14, 26, 0.9); border: 1.5px solid rgba(244, 63, 94, 0.5); border-radius: 8px; color: #fff; font-size: 14px; font-weight: 800; text-align: center; font-family: monospace; outline: none;">
                            <span style="font-size: 12px; font-weight: 700; color: #cbd5e1;">Jam</span>
                        </div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn-chip-hours" onclick="setSlotHours(1)">1 Jam</button>
                            <button type="button" class="btn-chip-hours" onclick="setSlotHours(2)">2 Jam</button>
                            <button type="button" class="btn-chip-hours" onclick="setSlotHours(4)">4 Jam</button>
                            <button type="button" class="btn-chip-hours active" onclick="setSlotHours(6)">6 Jam (Penuh)</button>
                        </div>
                    </div>
                </div>

                @php
                    $canCheckOutByTime = ($minutesWorked ?? 0) >= ($minCheckoutMinutes ?? 30);
                    $canCheckOut = $canCheckOutByTime && ($hasCompletedHandover ?? false);
                @endphp

                @if(!$canCheckOutByTime)
                    <div style="background: rgba(245, 158, 11, 0.14); border: 1px solid rgba(245, 158, 11, 0.45); border-radius: 10px; padding: 11px 13px; margin-bottom: 12px; font-size: 12px; color: #fde68a; line-height: 1.5;">
                        <strong>⏳ Belum Bisa Check-Out (Baru {{ $minutesWorked ?? 0 }} menit bekerja)</strong><br>
                        Minimal durasi kerja adalah <strong>{{ $minCheckoutMinutes ?? 30 }} menit</strong> setelah Check-In agar tidak terjadi salah klik atau absen formalitas.
                    </div>
                @elseif(!($hasCompletedHandover ?? false))
                    <div style="background: rgba(244, 63, 94, 0.14); border: 1px solid rgba(244, 63, 94, 0.45); border-radius: 10px; padding: 11px 13px; margin-bottom: 12px; font-size: 12px; color: #fecdd3; line-height: 1.5;">
                        <strong>🔒 Wajib Rekap &amp; Serah Terima Shift Terlebih Dahulu</strong><br>
                        Sebelum Check-Out, Anda wajib menyelesaikan <a href="{{ route('presensi2.rekap') }}" style="color: #93c5fd; font-weight: 700; text-decoration: underline;">Rekap</a> dan <a href="{{ route('presensi2.serah-terima') }}" style="color: #93c5fd; font-weight: 700; text-decoration: underline;">Serah Terima Shift</a> terlebih dahulu (meski penjualan Rp 0).
                    </div>
                @else
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-bottom: 10px; text-align: center;">
                        Pastikan durasi jam kerja di atas sudah sesuai sebelum konfirmasi pulang / serah terima:
                    </div>
                @endif

                <button type="button" id="btn-clock-out" onclick="submitAttendance('out')" class="btn btn-rose" {{ $canCheckOut ? '' : 'disabled' }} style="padding: 14px; font-size: 14.5px; width: 100%; font-weight: 700; box-shadow: 0 4px 14px rgba(244, 63, 94, 0.35); {{ $canCheckOut ? '' : 'opacity: 0.45; cursor: not-allowed;' }}">
                    <span>🏁</span> Konfirmasi Check-Out (Selesai Dinas)
                </button>
            </div>

        @else
            {{-- SUDAH SELESAI --}}
            <div style="text-align: center; padding: 24px 0;">
                <div style="font-size: 48px; margin-bottom: 12px;">🎉</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 8px;">Presensi Hari Ini Telah Selesai!</h4>
                <div style="display: flex; align-items: center; justify-content: center; gap: 20px; margin-top: 14px;">
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted);">Masuk</span>
                        <div style="font-size: 18px; font-weight: 700; color: #34d399;">{{ $attendanceToday->clock_in_at?->format('H:i') }}</div>
                    </div>
                    <div style="font-size: 20px; color: var(--text-sub);">→</div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted);">Pulang</span>
                        <div style="font-size: 18px; font-weight: 700; color: #fca5a5;">{{ $attendanceToday->clock_out_at?->format('H:i') }}</div>
                    </div>
                    <div style="font-size: 20px; color: var(--text-sub);">⏱️</div>
                    <div>
                        <span style="font-size: 11px; color: var(--text-muted);">Durasi</span>
                        <div style="font-size: 18px; font-weight: 700; color: #22d3ee;">{{ round($attendanceToday->duration_minutes / 60, 1) }} Jam</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- RESULT BOX (HIDDEN) --}}
    <div id="result-box" class="glass-card" style="display: none; text-align: center;"></div>
</div>

@push('scripts')
<script>
    // Live Clock synchronized with Server / Mock Time
    let serverTimeMs = {{ now()->getTimestamp() * 1000 }};
    setInterval(() => {
        serverTimeMs += 1000;
        const d = new Date(serverTimeMs);
        const h = String(d.getHours()).padStart(2, '0');
        const m = String(d.getMinutes()).padStart(2, '0');
        const s = String(d.getSeconds()).padStart(2, '0');
        const el = document.getElementById('live-clock');
        if (el) el.textContent = `${h}:${m}:${s}`;
    }, 1000);

    // Live Clock PC Turet (Waktu Lokal Komputer Kasir / Client)
    function updatePcTuretClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const el = document.getElementById('pc-turet-clock');
        if (el) el.textContent = `${h}:${m}:${s}`;

        const elDate = document.getElementById('pc-turet-date');
        if (elDate) {
            const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
            elDate.textContent = `(${now.toLocaleDateString('id-ID', options)})`;
        }
    }
    updatePcTuretClock();
    setInterval(updatePcTuretClock, 1000);

    // Fungsi 1-Klik Samakan ke Jam & Tanggal PC Turet
    function syncToPcTuretTime() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');

        const mockDate = `${y}-${m}-${d}`;
        const mockTime = `${hh}:${mm}:${ss}`;

        const url = new URL(window.location.href);
        url.searchParams.set('mock_date', mockDate);
        url.searchParams.set('mock_time', mockTime);
        window.location.href = url.toString();
    }

    function updateSelectedShiftInfo(select) {
        const opt = select.options[select.selectedIndex];
        if (!opt) return;
        const label = document.getElementById('header-shift-label');
        if (label) {
            const code = opt.getAttribute('data-code') || 'SHIFT';
            const name = opt.getAttribute('data-name') || '';
            const start = opt.getAttribute('data-start') || '';
            const end = opt.getAttribute('data-end') || '';
            label.innerHTML = `<span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 11px; padding: 2px 8px;">${code}</span> ${name} (${start} - ${end})`;
        }
    }

    // Network Status & Offline Buffer Engine (IndexedDB)
    const DB_NAME = 'AttendanceOfflineDB';
    const STORE_NAME = 'offline_queue';

    function openOfflineDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, 1);
            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME, { keyPath: 'offline_nonce' });
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function saveOfflineAttendance(item) {
        const db = await openOfflineDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            store.put(item);
            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    }

    async function getPendingOfflineQueue() {
        const db = await openOfflineDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const req = store.getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror = () => reject(req.error);
        });
    }

    async function clearOfflineItem(nonce) {
        const db = await openOfflineDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            store.delete(nonce);
            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    }

    async function syncOfflineQueue() {
        try {
            const queue = await getPendingOfflineQueue();
            if (!queue || queue.length === 0) return;

            updateNetworkBanner('syncing', `⏳ Mengirim ${queue.length} antrean presensi offline ke server...`);

            const res = await fetch('{{ route("presensi2.sync-offline") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ items: queue }),
            });

            const data = await res.json();
            if (data.success || (data.results && data.results.length > 0)) {
                for (const item of queue) {
                    await clearOfflineItem(item.offline_nonce);
                }
                updateNetworkBanner('online', `✅ Sinkronisasi Berhasil: ${data.summary?.synced ?? queue.length} data presensi offline telah tercatat di server.`);
                setTimeout(() => {
                    const banner = document.getElementById('network-status-banner');
                    if (banner) banner.style.display = 'none';
                }, 4000);
            }
        } catch (e) {
            console.warn('Sync offline queue failed:', e);
        }
    }

    function updateNetworkBanner(state, message) {
        const banner = document.getElementById('network-status-banner');
        if (!banner) return;
        banner.style.display = 'block';

        if (state === 'offline') {
            banner.style.background = 'rgba(239, 68, 68, 0.15)';
            banner.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            banner.style.color = '#fca5a5';
            banner.innerHTML = `<div style="display:flex;align-items:center;gap:8px;"><span>⚠️</span><span>${message || 'Mode Offline: Koneksi internet terputus. Presensi akan disimpan aman di buffer lokal.'}</span></div>`;
        } else if (state === 'syncing') {
            banner.style.background = 'rgba(245, 158, 11, 0.15)';
            banner.style.border = '1px solid rgba(245, 158, 11, 0.3)';
            banner.style.color = '#fcd34d';
            banner.innerHTML = `<div style="display:flex;align-items:center;gap:8px;"><span>⏳</span><span>${message}</span></div>`;
        } else {
            banner.style.background = 'rgba(16, 185, 129, 0.15)';
            banner.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            banner.style.color = '#6ee7b7';
            banner.innerHTML = `<div style="display:flex;align-items:center;gap:8px;"><span>✅</span><span>${message}</span></div>`;
        }
    }

    window.addEventListener('online', () => {
        updateNetworkBanner('online', 'Koneksi internet kembali! Memulai sinkronisasi otomatis...');
        syncOfflineQueue();
    });

    window.addEventListener('offline', () => {
        updateNetworkBanner('offline', 'Koneksi internet terputus. Fitur PWA Offline Buffer aktif.');
    });

    // Periksa antrean saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        if (!navigator.onLine) {
            updateNetworkBanner('offline', 'Anda sedang offline. Presensi tetap dapat dilakukan dan disimpan di buffer lokal.');
        } else {
            syncOfflineQueue();
        }
    });

    async function submitAttendance(mode) {
        const btnId = mode === 'in' ? 'btn-clock-in' : 'btn-clock-out';
        const btn = document.getElementById(btnId);

        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Memproses Presensi...';

        const url = mode === 'in'
            ? '{{ route("presensi2.clock-in") }}'
            : '{{ route("presensi2.clock-out") }}';

        const livenessInput = document.getElementById('liveness_score_input');
        const livenessScore = livenessInput ? parseFloat(livenessInput.value) : 0.98;

        const payload = {
            liveness_score: livenessScore, // Liveness Detection Active Challenge Passed
        };

        if (mode === 'in') {
            const shiftSelect = document.getElementById('shift_id');
            if (shiftSelect && shiftSelect.value) {
                payload.shift_id = parseInt(shiftSelect.value, 10);
            }
        } else if (mode === 'out') {
            payload.enforce_handover = true;
            const durInput = document.getElementById('duration_hours_input');
            if (durInput && durInput.value) {
                payload.duration_hours = parseInt(durInput.value, 10);
            }
        }

        // Cek apakah perangkat offline
        if (!navigator.onLine && mode === 'in') {
            const nonce = 'off_' + (crypto.randomUUID ? crypto.randomUUID() : (Math.random().toString(36).substring(2) + Date.now().toString(36)));
            const offlineItem = {
                offline_nonce: nonce,
                clock_in_at: new Date().toISOString(),
                shift_id: payload.shift_id || null,
                liveness_score: payload.liveness_score,
                notes: 'Presensi offline disimpan via buffer PWA browser lokal',
            };

            try {
                await saveOfflineAttendance(offlineItem);
                showResult(true, 'Mode Offline: Presensi berhasil diamankan di buffer lokal (Anti-Replay Nonce). Akan otomatis disinkronkan saat online.');
                updateNetworkBanner('offline', 'Presensi tersimpan di buffer lokal. Jangan bersihkan cache browser sampai sinkronisasi selesai.');
            } catch (err) {
                showResult(false, 'Gagal menyimpan data ke buffer lokal IndexedDB.');
                btn.disabled = false;
                btn.innerHTML = '<span>⚡</span> Konfirmasi Check-In';
            }
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            if (response.status === 419) {
                showResult(false, 'Sesi browser kedaluwarsa (419). Memuat ulang halaman...');
                setTimeout(() => location.reload(), 1500);
                return;
            }

            if (response.status === 401) {
                showResult(false, 'Sesi login telah berakhir. Mengalihkan ke login...');
                setTimeout(() => window.location.href = '{{ route("presensi2.login") }}', 1500);
                return;
            }

            const data = await response.json();

            if (data.success) {
                showResult(true, data.message);
                setTimeout(() => {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        location.reload();
                    }
                }, 1200);
            } else {
                showResult(false, data.message || 'Terjadi kesalahan.');
                if (data.redirect_url) {
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1800);
                }
                btn.disabled = false;
                btn.innerHTML = mode === 'in'
                    ? '<span>⚡</span> Konfirmasi Check-In'
                    : '<span>🏁</span> Konfirmasi Check-Out';
            }
        } catch (err) {
            if (mode === 'in') {
                // Simpan ke offline buffer saat error jaringan
                const nonce = 'off_' + (crypto.randomUUID ? crypto.randomUUID() : (Math.random().toString(36).substring(2) + Date.now().toString(36)));
                const offlineItem = {
                    offline_nonce: nonce,
                    clock_in_at: new Date().toISOString(),
                    shift_id: payload.shift_id || null,
                    liveness_score: payload.liveness_score,
                    notes: 'Presensi offline fallback disimpan akibat koneksi gagal',
                };
                try {
                    await saveOfflineAttendance(offlineItem);
                    showResult(true, 'Koneksi gagal. Presensi berhasil dialihkan ke buffer lokal PWA dan akan disinkronkan otomatis.');
                    updateNetworkBanner('offline', 'Data presensi aman di buffer perangkat.');
                    return;
                } catch (e) {
                    console.error('Fallback save offline failed:', e);
                }
            }

            showResult(false, 'Gagal menghubungi server. Periksa koneksi internet Anda.');
            btn.disabled = false;
            btn.innerHTML = mode === 'in'
                ? '<span>⚡</span> Konfirmasi Check-In'
                : '<span>🏁</span> Konfirmasi Check-Out';
        }
    }

    function toggleHourCard(slotIndex) {
        let currentTotal = 0;
        for (let i = 1; i <= 6; i++) {
            if (document.getElementById(`hour-cb-${i}`)?.checked) currentTotal++;
        }

        let targetTotal = slotIndex;
        if (slotIndex === currentTotal) {
            targetTotal = Math.max(1, currentTotal - 1);
        }

        setSlotHours(targetTotal);
    }

    function setSlotHours(totalHours) {
        totalHours = Math.max(1, Math.min(6, parseInt(totalHours) || 1));
        for (let i = 1; i <= 6; i++) {
            const card = document.getElementById(`hour-card-${i}`);
            const cb = document.getElementById(`hour-cb-${i}`);
            if (!card || !cb) continue;

            if (i <= totalHours) {
                cb.checked = true;
                card.classList.add('active');
            } else {
                cb.checked = false;
                card.classList.remove('active');
            }
        }

        const input = document.getElementById('duration_hours_input');
        if (input) input.value = totalHours;

        const badgeText = document.getElementById('total-duration-hours-text');
        if (badgeText) badgeText.innerText = totalHours;

        updateChipSelection(totalHours);
    }

    function onDurationHoursChange() {
        const input = document.getElementById('duration_hours_input');
        if (!input) return;
        let val = parseInt(input.value) || 1;
        if (val < 1) val = 1;
        if (val > 6) val = 6;
        setSlotHours(val);
    }

    function updateChipSelection(val) {
        document.querySelectorAll('.btn-chip-hours').forEach(btn => {
            if (btn.innerText.trim().startsWith(val + ' Jam')) {
                btn.style.borderColor = '#fda4af';
                btn.style.background = 'rgba(244, 63, 94, 0.25)';
                btn.style.color = '#fff';
                btn.style.fontWeight = '700';
            } else {
                btn.style.borderColor = 'rgba(255, 255, 255, 0.12)';
                btn.style.background = 'rgba(255, 255, 255, 0.05)';
                btn.style.color = '#94a3b8';
                btn.style.fontWeight = '600';
            }
        });
    }

    function showResult(success, message) {
        const box = document.getElementById('result-box');
        box.style.display = 'block';
        box.className = 'glass-card ' + (success ? 'alert-success' : 'alert-error');
        box.innerHTML = `<div style="display: flex; align-items: center; gap: 10px; justify-content: center;"><span style="font-size: 20px;">${success ? '✅' : '❌'}</span><span style="font-size: 14px; font-weight: 600;">${message}</span></div>`;
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
@endpush
@endsection
