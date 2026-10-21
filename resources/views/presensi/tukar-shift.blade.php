@extends('layouts.presensi')

@section('title', 'Tukar Shift Kerja')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <!-- Header -->
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 22px; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">
            Ajukan Tukar Shift
        </h1>
        <p style="font-size: 13px; color: #94a3b8;">
            Permohonan pertukaran jadwal shift dengan rekan kerja dalam cabang yang sama. Memerlukan persetujuan rekan kerja dan verifikasi supervisor.
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

    <!-- Form Pengajuan -->
    <div style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
        <form action="{{ route('presensi.tukar-shift.submit') }}" method="POST">
            @csrf

            <!-- Rekan Kerja Target -->
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Pilih Rekan Kerja (Cabang Sama) <span style="color: #f43f5e;">*</span>
                </label>
                <select name="target_employee_id" required
                    style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">
                    <option value="">-- Pilih Rekan Kerja --</option>
                    @foreach($colleagues as $colleague)
                        <option value="{{ $colleague->id }}">
                            {{ $colleague->name }} ({{ $colleague->role?->name ?? 'Staff' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Tukar Shift -->
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Tanggal Pertukaran Shift <span style="color: #f43f5e;">*</span>
                </label>
                <input type="date" name="swap_date" value="{{ old('swap_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                    style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">
            </div>

            <!-- Shift Asal & Shift Tujuan -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                        Shift Anda Saat Ini <span style="color: #f43f5e;">*</span>
                    </label>
                    <select name="requester_shift_id" required
                        style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                        Shift Tujuan yang Diminta <span style="color: #f43f5e;">*</span>
                    </label>
                    <select name="target_shift_id" required
                        style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px;">
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Alasan -->
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">
                    Alasan Tukar Shift <span style="color: #f43f5e;">*</span>
                </label>
                <textarea name="reason" rows="3" required placeholder="Contoh: Ada keperluan keluarga mendadak"
                    style="width: 100%; padding: 10px 14px; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #f8fafc; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <button type="submit"
                style="width: 100%; padding: 12px; background: linear-gradient(135deg, #0ea5e9, #0284c7); border: none; border-radius: 10px; color: #fff; font-size: 14px; font-weight: 700; cursor: pointer;">
                Kirim Permohonan Tukar Shift
            </button>
        </form>
    </div>

    <!-- Riwayat Pengajuan -->
    @if(isset($myRequests) && $myRequests->count() > 0)
        <div style="margin-top: 24px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #f8fafc; margin-bottom: 12px;">Riwayat Pengajuan Saya</h3>
            @foreach($myRequests as $req)
                <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 14px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <strong style="color: #38bdf8; font-size: 13px;">Kepada: {{ $req->targetEmployee?->name }}</strong>
                        <span style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                            {{ $req->status?->value ?? 'Pending' }}
                        </span>
                    </div>
                    <div style="font-size: 12px; color: #94a3b8;">
                        Tanggal: {{ $req->swap_date?->format('d M Y') }} | Shift: {{ $req->requesterShift?->name }} ➔ {{ $req->targetShift?->name }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
