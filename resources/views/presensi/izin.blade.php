@extends('layouts.presensi')

@section('title', 'Permohonan Izin / Cuti')

@section('content')
<div style="padding: 16px; max-width: 680px; margin: 0 auto; padding-bottom: 90px;">
    <div style="margin-bottom: 20px;">
        <h1 style="font-size: 22px; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">
            Permohonan Izin & Cuti
        </h1>
        <p style="font-size: 13px; color: #94a3b8;">
            Formulir pengajuan izin dinas, cuti tahunan, atau izin sakit.
        </p>
    </div>

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="glass-card">
        <form action="{{ route('presensi.izin.submit') }}" method="POST">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 13px; color: #cbd5e1; margin-bottom: 6px;">Jenis Izin / Cuti</label>
                <select name="leave_type" style="width: 100%; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 14px; color: #fff;">
                    <option value="sick">Sakit</option>
                    <option value="annual">Cuti Tahunan</option>
                    <option value="permission">Izin Keperluan</option>
                </select>
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 13px; color: #cbd5e1; margin-bottom: 6px;">Mulai Tanggal</label>
                <input type="date" name="start_date" required style="width: 100%; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 14px; color: #fff;">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 13px; color: #cbd5e1; margin-bottom: 6px;">Sampai Tanggal</label>
                <input type="date" name="end_date" required style="width: 100%; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 14px; color: #fff;">
            </div>
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 13px; color: #cbd5e1; margin-bottom: 6px;">Alasan Permohonan</label>
                <textarea name="reason" required rows="3" style="width: 100%; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 14px; color: #fff;"></textarea>
            </div>
            <button type="submit" class="btn btn-emerald">
                Kirim Pengajuan Permohonan
            </button>
        </form>
    </div>
</div>
@endsection
