@extends('layouts.presensi')

@section('title', 'Permohonan Izin / Cuti')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <!-- Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 6px;">
            <h1 style="font-size: 22px; font-weight: 700; color: #f8fafc; margin: 0;">
                Permohonan Izin & Cuti
            </h1>
            <span style="font-size: 11.5px; background: rgba(99, 102, 241, 0.2); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.4); padding: 4px 10px; border-radius: 999px;">
                👤 {{ $currentEmployee->name }}
            </span>
        </div>
        <p style="font-size: 13px; color: #94a3b8; margin: 0;">
            Formulir resmi pengajuan cuti tahunan, izin sakit, atau izin keperluan khusus dengan verifikasi manajemen.
        </p>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert-box alert-success" style="margin-bottom: 16px;">
            <span style="font-size: 16px;">✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-box alert-error" style="margin-bottom: 16px;">
            <span style="font-size: 16px;">⚠️</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert-box alert-error" style="margin-bottom: 16px; flex-direction: column; align-items: flex-start;">
            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700;">
                <span>⚠️</span> Ada kesalahan pada formulir pengajuan:
            </div>
            <ul style="margin: 6px 0 0 20px; font-size: 12.5px; line-height: 1.5;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulir Pengajuan Cuti / Izin -->
    <div class="glass-card">
        <h3 style="font-size: 15px; font-weight: 700; color: #e2e8f0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <span>📝</span> Formulir Pengajuan Baru
        </h3>

        <form action="{{ route('presensi.izin.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Jenis Izin / Cuti -->
            <div style="margin-bottom: 14px;">
                <label for="leave_type" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Jenis Permohonan <span style="color: #f43f5e;">*</span>
                </label>
                <select name="leave_type" id="leave_type" required style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 12px 14px; color: #fff; font-size: 13.5px; outline: none;">
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->value }}" {{ old('leave_type') === $type->value ? 'selected' : '' }}>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Rentang Tanggal (Mulai & Sampai) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label for="start_date" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                        Mulai Tanggal <span style="color: #f43f5e;">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="start_date" 
                        id="start_date" 
                        value="{{ old('start_date', date('Y-m-d')) }}" 
                        required 
                        onchange="calculateDays()"
                        style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 11px 12px; color: #fff; font-size: 13px; outline: none;"
                    >
                </div>
                <div>
                    <label for="end_date" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                        Sampai Tanggal <span style="color: #f43f5e;">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="end_date" 
                        id="end_date" 
                        value="{{ old('end_date', date('Y-m-d')) }}" 
                        required 
                        onchange="calculateDays()"
                        style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 11px 12px; color: #fff; font-size: 13px; outline: none;"
                    >
                </div>
            </div>

            <!-- Estimasi Durasi Hari -->
            <div id="day-calc-banner" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 10px; padding: 8px 12px; margin-bottom: 14px; font-size: 12px; color: #c7d2fe; display: flex; align-items: center; justify-content: space-between;">
                <span>⏱️ Durasi pengajuan:</span>
                <strong id="day-calc-text" style="color: #fff;">1 Hari Kalender</strong>
            </div>

            <!-- Alasan Permohonan -->
            <div style="margin-bottom: 14px;">
                <label for="reason" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Alasan Permohonan <span style="color: #f43f5e;">*</span>
                </label>
                <textarea 
                    name="reason" 
                    id="reason" 
                    rows="3" 
                    required 
                    placeholder="Contoh: Mengalami demam dan disarankan istirahat dokter / Ada acara keluarga mendesak..."
                    style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 11px 14px; color: #fff; font-size: 13px; outline: none; line-height: 1.5;"
                >{{ old('reason') }}</textarea>
            </div>

            <!-- Lampiran Berkas / Dokumen Pendukung -->
            <div style="margin-bottom: 18px;">
                <label for="attachment" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Lampiran Bukti / Surat Dokter <span style="font-weight: 400; color: #94a3b8;">(Opsional)</span>
                </label>
                <input 
                    type="file" 
                    name="attachment" 
                    id="attachment" 
                    accept=".jpg,.jpeg,.png,.pdf" 
                    style="width: 100%; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 10px 12px; color: #94a3b8; font-size: 12.5px; outline: none;"
                >
                <div style="font-size: 11.5px; color: #64748b; margin-top: 5px;">
                    Format berkas: JPG, PNG, atau PDF (Ukuran maksimal 4MB).
                </div>
            </div>

            <!-- Tombol Kirim -->
            <button type="submit" class="btn btn-emerald" style="padding: 13px; font-size: 14.5px; font-weight: 700;">
                📨 Kirim Pengajuan Permohonan
            </button>
        </form>
    </div>

    <!-- Riwayat Pengajuan Izin & Cuti -->
    <div style="margin-top: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #f8fafc; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
            <span>📋 Riwayat Pengajuan Saya</span>
            <span style="font-size: 12px; color: #94a3b8; font-weight: normal;">Total: {{ $leaveRequests->count() }} pengajuan</span>
        </h3>

        @forelse($leaveRequests as $req)
            <div class="glass-card" style="padding: 16px; margin-bottom: 12px; border-left: 4px solid {{ $req->status->value === 'approved' ? '#10b981' : ($req->status->value === 'rejected' ? '#f43f5e' : '#f59e0b') }};">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 8px;">
                    <div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #fff;">
                            {{ $req->leave_type->label() }}
                        </div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">
                            📅 {{ \Carbon\Carbon::parse($req->start_date)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($req->end_date)->translatedFormat('d M Y') }}
                            <span style="color: #cbd5e1; font-weight: 600;">({{ $req->total_days }} Hari)</span>
                        </div>
                    </div>
                    <div>
                        @if($req->status->value === 'approved')
                            <span class="badge badge-present" style="font-size: 11px;">✓ Disetujui</span>
                        @elseif($req->status->value === 'rejected')
                            <span class="badge badge-absent" style="font-size: 11px;">✕ Ditolak</span>
                        @else
                            <span class="badge badge-late" style="font-size: 11px;">⏳ Menunggu</span>
                        @endif
                    </div>
                </div>

                <div style="background: rgba(15, 23, 42, 0.5); padding: 10px 12px; border-radius: 10px; font-size: 12.5px; color: #e2e8f0; margin-bottom: 8px; line-height: 1.45;">
                    <div style="font-size: 11px; color: #94a3b8; margin-bottom: 2px; font-weight: 600;">Alasan:</div>
                    {{ $req->reason }}
                </div>

                @if($req->rejection_note)
                    <div style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.3); padding: 8px 12px; border-radius: 10px; font-size: 12px; color: #fda4af; margin-bottom: 8px;">
                        <strong>Catatan Penolakan:</strong> {{ $req->rejection_note }}
                    </div>
                @endif

                @if($req->approver)
                    <div style="font-size: 11px; color: #10b981; margin-bottom: 6px;">
                        ✓ Disetujui oleh: <strong>{{ $req->approver->name }}</strong> ({{ \Carbon\Carbon::parse($req->approved_at)->translatedFormat('d M Y H:i') }})
                    </div>
                @endif

                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: #64748b; padding-top: 6px; border-top: 1px solid rgba(255, 255, 255, 0.05);">
                    <span>Diajukan: {{ $req->created_at?->diffForHumans() ?? '-' }}</span>
                    @if($req->attachment_path)
                        <a href="{{ asset($req->attachment_path) }}" target="_blank" style="color: #38bdf8; text-decoration: none; font-weight: 600;">
                            📎 Buka Lampiran
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="glass-card" style="text-align: center; padding: 32px 20px; color: #94a3b8;">
                <div style="font-size: 32px; margin-bottom: 8px;">📭</div>
                <div style="font-size: 14px; font-weight: 600; color: #e2e8f0; margin-bottom: 4px;">
                    Belum Ada Riwayat Permohonan
                </div>
                <div style="font-size: 12px;">
                    Permohonan izin atau cuti yang Anda ajukan akan tercatat dan ditampilkan di sini.
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
    function calculateDays() {
        const startVal = document.getElementById('start_date').value;
        const endVal = document.getElementById('end_date').value;
        const textElem = document.getElementById('day-calc-text');

        if (!startVal || !endVal) return;

        const start = new Date(startVal);
        const end = new Date(endVal);

        if (end < start) {
            textElem.innerText = 'Tanggal selesai tidak valid (sebelum tanggal mulai)';
            textElem.style.color = '#f87171';
            return;
        }

        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        textElem.innerText = diffDays + ' Hari Kalender';
        textElem.style.color = '#fff';
    }

    document.addEventListener('DOMContentLoaded', calculateDays);
</script>
@endsection
