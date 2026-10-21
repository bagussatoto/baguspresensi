@extends('layouts.presensi')

@section('title', 'Koreksi Presensi')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <!-- Header -->
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 22px; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">
            Pengajuan Koreksi Presensi
        </h1>
        <p style="font-size: 13px; color: #94a3b8;">
            Gunakan formulir ini jika terjadi lupa absen, baterai HP habis, atau kendala teknis saat dinas.
        </p>
    </div>

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.4); color: #f87171; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            ⚠ {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.4); color: #f87171; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
        <form action="{{ route('presensi.koreksi.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Tanggal Kerja -->
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Tanggal Kerja yang Dikoreksi <span style="color: #f43f5e;">*</span>
                </label>
                <input type="date" name="work_date" value="{{ old('work_date', $selectedDate ?? date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                    style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">
            </div>

            <!-- Shift Kerja -->
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Shift Kerja <span style="color: #f43f5e;">*</span>
                </label>
                <select name="shift_id" required
                    style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">
                    <option value="">-- Pilih Shift --</option>
                    @foreach($shifts as $s)
                        <option value="{{ $s->id }}" {{ old('shift_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Jenis Koreksi -->
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Bagian yang Perlu Dikoreksi <span style="color: #f43f5e;">*</span>
                </label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; background: rgba(15, 23, 42, 0.6); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.06); font-size: 12px; color: #cbd5e1; cursor: pointer;">
                        <input type="radio" name="correction_type" value="both" {{ old('correction_type', 'both') === 'both' ? 'checked' : '' }}>
                        Masuk & Pulang
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; background: rgba(15, 23, 42, 0.6); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.06); font-size: 12px; color: #cbd5e1; cursor: pointer;">
                        <input type="radio" name="correction_type" value="clock_in" {{ old('correction_type') === 'clock_in' ? 'checked' : '' }}>
                        Masuk Saja
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; background: rgba(15, 23, 42, 0.6); padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.06); font-size: 12px; color: #cbd5e1; cursor: pointer;">
                        <input type="radio" name="correction_type" value="clock_out" {{ old('correction_type') === 'clock_out' ? 'checked' : '' }}>
                        Pulang Saja
                    </label>
                </div>
            </div>

            <!-- Jam Usulan -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                        Jam Masuk Riil
                    </label>
                    <input type="time" name="clock_in_time" value="{{ old('clock_in_time', '07:00') }}"
                        style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                        Jam Pulang Riil
                    </label>
                    <input type="time" name="clock_out_time" value="{{ old('clock_out_time', '13:00') }}"
                        style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">
                </div>
            </div>

            <!-- Alasan -->
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Alasan / Justifikasi Koreksi <span style="color: #f43f5e;">*</span>
                </label>
                <textarea name="reason" rows="3" required placeholder="Contoh: HP kehabisan baterai saat dinas shift pagi, hadir tepat waktu..."
                    style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">{{ old('reason') }}</textarea>
            </div>

            <!-- Bukti Pendukung -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Foto Bukti Pendukung (Opsional)
                </label>
                <input type="file" name="attachment" accept="image/*,.pdf"
                    style="width: 100%; padding: 8px 12px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94a3b8; font-size: 13px;">
                <span style="font-size: 11px; color: #64748b;">Maksimal 4 MB (.jpg, .png, .pdf)</span>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                style="width: 100%; padding: 12px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);">
                Kirim Pengajuan Koreksi
            </button>
        </form>
    </div>

    <!-- Riwayat Section -->
    <div>
        <h2 style="font-size: 16px; font-weight: 700; color: #f8fafc; margin-bottom: 12px;">
            Riwayat Koreksi Presensi
        </h2>

        @forelse($corrections as $corr)
            <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 14px; margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div>
                        <span style="font-weight: 600; color: #f8fafc; font-size: 14px;">
                            {{ $corr->work_date->format('d M Y') }}
                        </span>
                        <span style="font-size: 12px; color: #94a3b8; margin-left: 6px;">
                            ({{ $corr->shift?->name ?? 'Shift' }})
                        </span>
                    </div>
                    <div>
                        @if($corr->status === App\Domains\Attendance\Enums\ApprovalStatus::Approved)
                            <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                                Disetujui
                            </span>
                        @elseif($corr->status === App\Domains\Attendance\Enums\ApprovalStatus::Rejected)
                            <span style="background: rgba(244, 63, 94, 0.2); color: #f87171; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                                Ditolak
                            </span>
                        @else
                            <span style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                                Menunggu Approval
                            </span>
                        @endif
                    </div>
                </div>

                <div style="font-size: 12px; color: #cbd5e1; margin-bottom: 6px;">
                    <strong>Usulan:</strong> 
                    {{ $corr->proposed_clock_in_at ? $corr->proposed_clock_in_at->format('H:i') : '-' }} s/d 
                    {{ $corr->proposed_clock_out_at ? $corr->proposed_clock_out_at->format('H:i') : '-' }}
                </div>

                <div style="font-size: 12px; color: #94a3b8; font-style: italic;">
                    "{{ $corr->reason }}"
                </div>

                @if($corr->rejection_note)
                    <div style="margin-top: 6px; padding-top: 6px; border-top: 1px dashed rgba(255,255,255,0.08); font-size: 11px; color: #f87171;">
                        Catatan Admin: {{ $corr->rejection_note }}
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align: center; padding: 30px; color: #64748b; font-size: 13px;">
                Belum ada riwayat pengajuan koreksi presensi.
            </div>
        @endforelse
    </div>
</div>
@endsection
