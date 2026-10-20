<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit SoT Presensi #{{ $record->id }} — Maya Graha Kencana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body { font-family: 'Roboto', sans-serif; background: #f1f5f9; color: #1e293b; margin: 0; padding: 24px; font-size: 13px; }
        .audit-container { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
        .btn-back { color: #94a3b8; text-decoration: none; font-size: 12px; font-weight: 600; padding: 6px 12px; border: 1px solid #475569; border-radius: 6px; }
        .btn-back:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .content { padding: 24px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
        .card-title { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #475569; margin-bottom: 12px; letter-spacing: 0.5px; }
        .row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #e2e8f0; font-size: 12px; }
        .row:last-child { border-bottom: none; }
        .label { color: #64748b; }
        .value { font-weight: 600; color: #0f172a; text-align: right; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="audit-container">
        <div class="header">
            <div>
                <h2 style="margin: 0; font-size: 18px; font-weight: 700;">🛡️ Laporan Audit SoT Presensi #{{ $record->id }}</h2>
                <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Bukti Kehadiran Karyawan Otoritatif (ISO 27001)</div>
            </div>
            <a href="{{ route('admin.presensi', ['date' => $record->work_date->toDateString()]) }}" class="btn-back">← Kembali ke Monitoring</a>
        </div>
        <div class="content">
            <div class="grid">
                <div class="card">
                    <div class="card-title">👤 Master Data Karyawan (SoT)</div>
                    <div class="row"><span class="label">Nama:</span><span class="value">{{ $employee->name ?? '-' }}</span></div>
                    <div class="row"><span class="label">NIK / Kode:</span><span class="value">{{ $employee->code ?? '-' }}</span></div>
                    <div class="row"><span class="label">Jabatan:</span><span class="value">{{ $employee->position ?? '-' }} ({{ $employee->role ?? 'Staff' }})</span></div>
                    <div class="row"><span class="label">Cabang Resmi:</span><span class="value">{{ $branch->name ?? '-' }}</span></div>
                    <div class="row"><span class="label">Tanggal Kerja:</span><span class="value">{{ $record->work_date ? $record->work_date->format('d/m/Y') : '-' }}</span></div>
                </div>

                <div class="card">
                    <div class="card-title">⏰ Kepatuhan Jam Kerja</div>
                    <div class="row"><span class="label">Shift:</span><span class="value">{{ $shift->name ?? '-' }} ({{ substr($shift->start_time ?? '', 0, 5) }} - {{ substr($shift->end_time ?? '', 0, 5) }})</span></div>
                    <div class="row"><span class="label">Clock In:</span><span class="value">{{ $record->clock_in_at ? $record->clock_in_at->format('H:i:s') : '-' }}</span></div>
                    <div class="row"><span class="label">Clock Out:</span><span class="value">{{ $record->clock_out_at ? $record->clock_out_at->format('H:i:s') : 'Belum Pulang' }}</span></div>
                    <div class="row"><span class="label">Status Kehadiran:</span><span class="value" style="color: #2563eb;">{{ strtoupper($record->status?->value ?? '-') }}</span></div>
                    <div class="row"><span class="label">Keterlambatan:</span><span class="value">{{ $record->late_minutes }} Menit</span></div>
                    <div class="row"><span class="label">Jam Lembur:</span><span class="value">{{ round(($record->overtime_minutes ?? 0) / 60, 1) }} Jam</span></div>
                </div>
            </div>

            <!-- BUKTI FOTO SELFIE -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-title">📸 Bukti Visual (Selfie In & Out)</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: center;">
                    <div style="background: #fff; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div style="font-weight: 700; color: #047857; margin-bottom: 8px;">Selfie Masuk (Clock-In)</div>
                        @if($clock_in_photo_url)
                            <img src="{{ $clock_in_photo_url }}" style="max-height: 220px; max-width: 100%; border-radius: 6px; object-fit: contain;">
                        @else
                            <div style="height: 160px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">Tidak ada foto masuk</div>
                        @endif
                    </div>
                    <div style="background: #fff; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div style="font-weight: 700; color: #b91c1c; margin-bottom: 8px;">Selfie Pulang (Clock-Out)</div>
                        @if($clock_out_photo_url)
                            <img src="{{ $clock_out_photo_url }}" style="max-height: 220px; max-width: 100%; border-radius: 6px; object-fit: contain;">
                        @else
                            <div style="height: 160px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">Belum pulang / tanpa foto</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- GEOFENCE & RADAR MAP -->
            @if($record->clock_in_lat && $record->clock_in_lng)
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-title">📍 Visualisasi Radar Geofence GPS</div>
                <div id="directMap" style="height: 350px; width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 12px;"></div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; color: #475569;">
                    <span>Jarak: <strong>{{ number_format($record->clock_in_distance_meters, 1) }}m</strong> (Batas Radius: {{ $max_radius }}m)</span>
                    <span style="font-weight: 700; color: {{ $is_geofence_valid ? '#166534' : '#dc2626' }}">{{ $is_geofence_valid ? '✓ Presensi di Dalam Radius Cabang' : '✗ Presensi di Luar Radius Cabang' }}</span>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const branchCoords = [{{ $branch?->latitude ?? -6.2 }}, {{ $branch?->longitude ?? 106.8 }}];
                    const empCoords = [{{ $record->clock_in_lat }}, {{ $record->clock_in_lng }}];
                    const map = L.map('directMap');
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                    L.marker(branchCoords).addTo(map).bindPopup("<b>🏢 {{ addslashes($branch?->name ?? 'Cabang') }}</b>").openPopup();
                    L.circle(branchCoords, {
                        color: '{{ $is_geofence_valid ? '#10b981' : '#f59e0b' }}',
                        fillColor: '{{ $is_geofence_valid ? '#10b981' : '#f59e0b' }}',
                        fillOpacity: 0.15,
                        radius: {{ $max_radius }}
                    }).addTo(map);
                    L.marker(empCoords).addTo(map).bindPopup("<b>👤 {{ addslashes($employee?->name ?? 'Karyawan') }}</b>");
                    L.polyline([branchCoords, empCoords], { color: '{{ $is_geofence_valid ? '#10b981' : '#ef4444' }}', dashArray: '5, 8' }).addTo(map);

                    const group = new L.featureGroup([L.marker(branchCoords), L.marker(empCoords)]);
                    map.fitBounds(group.getBounds().pad(0.3));
                });
            </script>
            @endif

            <!-- KETERHUBUNGAN KASIR POS -->
            @if($pos_session)
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-title">🏪 Keterhubungan Kasir POS & Blind Cash Count</div>
                <div class="row"><span class="label">Status Shift Kasir:</span><span class="value">{{ strtoupper($pos_session->status) }}</span></div>
                <div class="row"><span class="label">Kas Fisik Laci (Blind Count):</span><span class="value">Rp {{ number_format($pos_session->blind_cash_count, 0, ',', '.') }}</span></div>
                <div class="row"><span class="label">Target Kas Sistem:</span><span class="value">Rp {{ number_format($pos_session->system_expected_cash, 0, ',', '.') }}</span></div>
                <div class="row"><span class="label">Selisih Kas:</span><span class="value" style="color: {{ (float)$pos_session->cash_difference == 0 ? '#166534' : '#dc2626' }};">Rp {{ number_format($pos_session->cash_difference, 0, ',', '.') }}</span></div>
                <div class="row"><span class="label">Catatan Serah Terima:</span><span class="value">{{ $pos_session->handover_notes ?: '-' }}</span></div>
            </div>
            @endif

            <!-- WORM AUDIT LOGS -->
            <div class="card">
                <div class="card-title">🛡️ Jejak WORM Audit Trail ISO 27001</div>
                @if(count($audit_logs) > 0)
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr style="border-bottom: 1px solid #cbd5e1; text-align: left; color: #64748b;">
                                <th style="padding: 6px;">Waktu</th>
                                <th style="padding: 6px;">Aksi</th>
                                <th style="padding: 6px;">Data Nilai Baru</th>
                                <th style="padding: 6px;">IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($audit_logs as $log)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 6px;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td style="padding: 6px;"><span class="badge" style="background:#e0e7ff;color:#3730a3;">{{ $log->action }}</span></td>
                                    <td style="padding: 6px; font-family: monospace;">{{ json_encode($log->new_values) }}</td>
                                    <td style="padding: 6px; color: #64748b;">{{ $log->ip_address }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="color: #64748b; font-size: 11px; text-align: center; padding: 8px;">
                        Belum ada eskalasi koreksi atau modifikasi audit pada rekaman presensi ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
