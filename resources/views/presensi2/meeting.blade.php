@extends('layouts.presensi2')

@section('title', 'Meeting — Presensi Operasional MGK')

@section('content')
<div class="meeting-portal-page">
    {{-- TOP NAVIGATION BAR / BACK BUTTON --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
        <a href="{{ route('presensi2.layanan.login', ['service' => 'meeting']) }}" class="btn-back-link" style="display: inline-flex; align-items: center; gap: 8px; color: #94a3b8; font-size: 13px; font-weight: 600; text-decoration: none; padding: 8px 14px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: 12px; transition: all 0.2s ease;">
            <span>&larr;</span>
            <span>Kembali ke Portal Layanan</span>
        </a>

        @if(session()->has('presensi2_employee_id'))
            <a href="{{ route('presensi2.index') }}" style="display: inline-flex; align-items: center; gap: 6px; color: #38bdf8; font-size: 12px; font-weight: 600; text-decoration: none;">
                <span>🏠</span>
                <span>Beranda Presensi</span>
            </a>
        @endif
    </div>

    {{-- HEADER OUTLET & STATUS --}}
    <div class="glass-card" style="padding: 16px 18px; margin-bottom: 14px; border-color: rgba(14, 165, 233, 0.25);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted);">
                    Rapat & Koordinasi Kerja
                </span>
                <h2 style="font-size: 18px; font-weight: 800; color: #fff; margin-top: 2px; display: flex; align-items: center; gap: 8px;">
                    <span>🤝</span> Presensi Meeting
                </h2>
            </div>
            <div style="text-align: right;">
                <span class="badge" style="background: rgba(14, 165, 233, 0.18); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.35); font-size: 11px; padding: 5px 12px;">
                    🤝 Rapat Operasional
                </span>
            </div>
        </div>

        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--border-glass); display: flex; align-items: center; justify-content: space-between; font-size: 12px; flex-wrap: wrap; gap: 6px;">
            <span style="color: var(--text-sub);">Lokasi / Cabang:</span>
            <strong style="color: #e5e7eb; display: inline-flex; align-items: center; gap: 6px;">
                <span>📍</span> {{ $currentEmployee->branch->name ?? 'Maya Graha Kencana Pusat' }}
            </strong>
        </div>
    </div>

    {{-- LIVE DIGITAL CLOCK (SAMA SEPERTI HALAMAN PRESENSI) --}}
    <div class="glass-card" style="text-align: center; padding: 24px 18px; margin-bottom: 16px; background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.08) 0%, rgba(17, 24, 39, 0.9) 100%);">
        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 6px;">
            Waktu Presensi Meeting (WIB)
        </div>
        <div id="live-clock" style="font-size: 46px; font-weight: 800; font-family: 'Outfit', monospace; color: #fff; letter-spacing: 2px; text-shadow: 0 0 24px rgba(14, 165, 233, 0.45);">
            {{ now()->format('H:i:s') }}
        </div>
        <div style="font-size: 13px; color: var(--text-sub); margin-top: 4px; font-weight: 500;">
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    {{-- STATS SUMMARY ROW --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px;">
        <div class="glass-card" style="padding: 12px; text-align: center; margin-bottom: 0;">
            <div style="font-size: 20px; font-weight: 800; color: #38bdf8; font-family: 'Outfit';">{{ $stats['total_staff'] }}</div>
            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Peserta Hadir</div>
        </div>
        <div class="glass-card" style="padding: 12px; text-align: center; margin-bottom: 0;">
            <div style="font-size: 20px; font-weight: 800; color: #a78bfa; font-family: 'Outfit';">{{ $stats['total_sessions'] }}</div>
            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Sesi Rapat</div>
        </div>
        <div class="glass-card" style="padding: 12px; text-align: center; margin-bottom: 0;">
            <div style="font-size: 20px; font-weight: 800; color: #34d399; font-family: 'Outfit';">{{ round($stats['total_duration_minutes'] / 60, 1) }} Jam</div>
            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; margin-top: 2px;">Total Jam Rapat</div>
        </div>
    </div>

    {{-- KARTU IDENTITAS KARYAWAN TERVERIFIKASI (TIDAK BISA ASAL PILIH NAMA) --}}
    <div class="glass-card" style="padding: 14px 16px; margin-bottom: 16px; border-color: rgba(14, 165, 233, 0.3); background: rgba(15, 23, 42, 0.85);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #0284c7, #0ea5e9); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);">
                    👤
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                        Karyawan Terautentikasi:
                    </span>
                    <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-top: 1px;">
                        {{ $currentEmployee->name }}
                        <span style="font-size: 11px; font-weight: normal; color: #38bdf8;">({{ $currentEmployee->code ?? 'Karyawan' }})</span>
                    </h3>
                    <div style="font-size: 11px; color: var(--text-sub);">
                        {{ $currentEmployee->position ?? 'Staf Operasional' }} &bull; 📍 {{ $currentEmployee->branch->name ?? 'Cabang MGK' }}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 11px; padding: 4px 10px;">
                    🔒 Terverifikasi Login
                </span>
                <a href="{{ route('presensi2.layanan.logout', ['service' => 'meeting']) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 10px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #fca5a5; font-size: 11px; font-weight: 600; text-decoration: none;">
                    <span>🚪</span> Ganti Akun
                </a>
            </div>
        </div>
    </div>

    {{-- FORMULIR PRESENSI MEETING --}}
    <div class="glass-card" style="padding: 22px 18px; margin-bottom: 18px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
            <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(14, 165, 233, 0.18); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                🤝
            </div>
            <div>
                <h3 style="font-size: 16px; font-weight: 700; color: #fff;">Presensi Kehadiran Meeting</h3>
                <p style="font-size: 12px; color: var(--text-muted);">Masukkan Password / PIN konfirmasi dan detail rapat.</p>
            </div>
        </div>

        <form action="{{ route('presensi2.meeting.store') }}" method="POST" id="form-meeting-attendance">
            @csrf

            {{-- VERIFIKASI PASSWORD / PIN KEAMANAN --}}
            <div style="margin-bottom: 18px; padding: 14px 16px; border-radius: 14px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(14, 165, 233, 0.35);">
                <label for="credential" class="form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="display: flex; align-items: center; gap: 6px; color: #fff;">
                        <span>🔐</span> Password atau PIN 6-Digit Anda:
                    </span>
                    <span style="font-size: 11px; color: #38bdf8; font-weight: normal;">(Wajib untuk Validasi)</span>
                </label>
                <div style="position: relative;">
                    <input type="password" name="credential" id="credential" class="form-input" placeholder="Masukkan Password atau PIN 6-Digit Akun Anda" required minlength="4" autocomplete="current-password" style="padding-left: 42px;" autofocus>
                    <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;">🔑</span>
                </div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
                    Konfirmasi kredensial keamanan untuk memvalidasi presensi kehadiran meeting atas nama Anda.
                </div>
            </div>

            {{-- TOPIK / AGENDA MEETING --}}
            <div style="margin-bottom: 16px;">
                <label for="title" class="form-label">🤝 Judul / Topik Agenda Rapat:</label>
                <input type="text" name="title" id="title" class="form-input" placeholder="Contoh: Briefing Shift & Evaluasi Kasir" required value="{{ old('title', 'Briefing Operasional Shift & Koordinasi Tim') }}">

                {{-- QUICK SELECT CHIPS --}}
                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px;">
                    <button type="button" class="btn-chip" onclick="setMeetingTitle('Briefing Operasional Shift & Evaluasi Kasir')">⚡ Briefing Shift</button>
                    <button type="button" class="btn-chip" onclick="setMeetingTitle('Rapat Koordinasi Mingguan Tim Outlet')">📅 Rapat Mingguan</button>
                    <button type="button" class="btn-chip" onclick="setMeetingTitle('Evaluasi Target Layanan & SOP Hospitality')">⭐ SOP & Layanan</button>
                    <button type="button" class="btn-chip" onclick="setMeetingTitle('Koordinasi Kendala Teknis Hardware & Jaringan')">🖥️ Kendala Teknis</button>
                    <button type="button" class="btn-chip" onclick="setMeetingTitle('Rapat Bulanan & Review Kinerja Karyawan')">📊 Rapat Bulanan</button>
                </div>
            </div>

            {{-- 4. WAKTU MULAI & SELESAI --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label for="start_time" class="form-label">⏰ Jam Mulai:</label>
                    <input type="time" name="start_time" id="start_time" class="form-input" value="{{ now()->format('H:i') }}">
                </div>
                <div>
                    <label for="end_time" class="form-label">🏁 Jam Selesai:</label>
                    <input type="time" name="end_time" id="end_time" class="form-input" value="{{ now()->addHour()->format('H:i') }}">
                </div>
            </div>

            {{-- 5. DURASI MENIT --}}
            <div style="margin-bottom: 16px;">
                <label for="duration_minutes" class="form-label">⏱️ Durasi Meeting (Menit):</label>
                <input type="number" name="duration_minutes" id="duration_minutes" class="form-input" min="5" max="720" value="60">
            </div>

            {{-- 6. NOTULENSI / CATATAN --}}
            <div style="margin-bottom: 20px;">
                <label for="notes" class="form-label">📝 Ringkasan / Poin Pembahasan Rapat (Opsional):</label>
                <textarea name="notes" id="notes" rows="2" class="form-input" placeholder="Tuliskan poin hasil rapat, kesepakatan tim, atau arahan manajerial..."></textarea>
            </div>

            {{-- SUBMIT BUTTON --}}
            <button type="submit" class="btn" style="padding: 16px; font-size: 15px; font-weight: 700; width: 100%; border-radius: 14px; background: linear-gradient(135deg, #0284c7, #0ea5e9); color: #fff; box-shadow: 0 8px 24px rgba(14, 165, 233, 0.4);">
                <span>🤝</span> Konfirmasi Hadir Meeting
            </button>
        </form>
    </div>

    {{-- RIWAYAT LOG KEHADIRAN MEETING HARI INI --}}
    <div class="glass-card" style="padding: 18px 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                <span>📋</span> Log Hadir Meeting Hari Ini ({{ now()->format('d/m/Y') }})
            </h4>
            <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8;">
                {{ $todayAttendances->count() }} Tercatat
            </span>
        </div>

        @if($todayAttendances->isEmpty())
            <div style="text-align: center; padding: 24px 10px; color: var(--text-muted); font-size: 13px;">
                <div style="font-size: 32px; margin-bottom: 6px;">🤝</div>
                Belum ada staf yang mencatat presensi kehadiran meeting hari ini.
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($todayAttendances as $item)
                    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-glass); border-radius: 12px; padding: 12px 14px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                            <strong style="color: #fff; font-size: 13px;">{{ $item->employee->name ?? 'Staf' }}</strong>
                            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 10px; padding: 2px 8px;">
                                ✅ Hadir Rapat
                            </span>
                        </div>
                        <div style="font-size: 12px; color: #38bdf8; font-weight: 600; margin-bottom: 4px;">
                            {{ $item->title }}
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: var(--text-muted);">
                            <span>📍 {{ $item->branch->name ?? 'MGK' }}</span>
                            <span>⏱️ {{ substr($item->start_time ?? '00:00', 0, 5) }} - {{ substr($item->end_time ?? '00:00', 0, 5) }} ({{ $item->duration_minutes }} mnt)</span>
                        </div>
                        @if($item->notes)
                            <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed var(--border-glass); font-size: 11px; color: var(--text-sub); font-style: italic;">
                                💬 {{ $item->notes }}
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
        background: rgba(14, 165, 233, 0.15);
        border-color: rgba(14, 165, 233, 0.4);
        color: #38bdf8;
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

    function setMeetingTitle(val) {
        const input = document.getElementById('title');
        if (input) input.value = val;
    }
</script>
@endpush
@endsection
