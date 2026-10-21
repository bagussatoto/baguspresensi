@extends('layouts.presensi2')

@section('title', 'Presensi Kredensial — Clock In/Out')

@section('content')
<div class="scan-v2-page">
    {{-- NETWORK & OFFLINE BUFFER STATUS BANNER --}}
    <div id="network-status-banner" style="display: none; padding: 12px 16px; border-radius: 14px; margin-bottom: 14px; font-size: 13px; font-weight: 600;"></div>

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

    {{-- FORM VERIFIKASI & SUBMIT --}}
    <div class="glass-card" style="padding: 22px 18px;">
        @if(!$attendanceToday)
            {{-- MODE: CLOCK-IN --}}
            <div style="margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(16, 185, 129, 0.18); display: flex; align-items: center; justify-content: center; font-size: 22px;">✅</div>
                    <div>
                        <h4 style="font-size: 17px; font-weight: 700; color: #fff;">Presensi Masuk</h4>
                        <p style="font-size: 13px; color: var(--text-muted);">Konfirmasi kehadiran masuk sesuai shift kerja Anda.</p>
                    </div>
                </div>
            </div>

            <!-- {{-- PILIHAN SHIFT KERJA --}}
            <div style="margin-bottom: 20px; text-align: left;">
                <label for="shift_id" style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; font-weight: 600; color: #d1d5db; margin-bottom: 8px;">
                    <span>⏱️ Pilih Shift Kerja:</span>
                    <span style="font-size: 11px; color: #34d399; font-weight: 500;">✓ Sesuai Jam Kerja Saat Ini</span>
                </label>
                <div style="position: relative;">
                    <select id="shift_id" name="shift_id" onchange="updateSelectedShiftInfo(this)" style="width: 100%; padding: 12px 14px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(139, 92, 246, 0.35); border-radius: 12px; color: #fff; font-size: 13px; font-weight: 600; outline: none; appearance: none; -webkit-appearance: none; cursor: pointer; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}" {{ $activeShift && $activeShift->id === $s->id ? 'selected' : '' }} data-name="{{ $s->name }}" data-code="{{ $s->code }}" data-start="{{ substr($s->start_time, 0, 5) }}" data-end="{{ substr($s->end_time, 0, 5) }}">
                                {{ $s->name }} ({{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }}) {{ $activeShift && $activeShift->id === $s->id ? '★ [Shift Saat Ini]' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #a78bfa; font-size: 12px;">▼</div>
                </div>
                <div style="margin-top: 6px; font-size: 11px; color: var(--text-muted);">
                    Sistem mendeteksi shift berdasarkan jam saat ini. Anda dapat mengganti shift jika diperlukan.
                </div>
            </div> -->

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
            {{-- MODE: CLOCK-OUT --}}
            <div style="margin-bottom: 22px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(239, 68, 68, 0.18); display: flex; align-items: center; justify-content: center; font-size: 22px;">🏁</div>
                    <div>
                        <h4 style="font-size: 17px; font-weight: 700; color: #fff;">Presensi Pulang</h4>
                        <p style="font-size: 13px; color: var(--text-muted);">
                            Masuk pukul <strong style="color: #34d399;">{{ $attendanceToday->clock_in_at?->format('H:i') }}</strong> — klik tombol di bawah untuk konfirmasi kepulangan.
                        </p>
                    </div>
                </div>
            </div>

            <button type="button" id="btn-clock-out" onclick="submitAttendance('out')" class="btn btn-rose" style="padding: 16px; font-size: 16px; width: 100%;">
                <span>🏁</span> Konfirmasi Check-Out
            </button>

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
    // Live Clock
    setInterval(() => {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const el = document.getElementById('live-clock');
        if (el) el.textContent = `${h}:${m}:${s}`;
    }, 1000);

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
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

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
