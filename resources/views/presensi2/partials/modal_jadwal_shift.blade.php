@php
    $activeScheduleImg = rescue(fn () => \App\Models\ShiftScheduleImage::where('is_active', true)->latest()->first()
        ?? \App\Models\ShiftScheduleImage::latest()->first());

    $staticInfoPath = public_path('uploads/jadwal/info.json');
    $staticJadwalInfo = file_exists($staticInfoPath)
        ? json_decode((string) file_get_contents($staticInfoPath), true)
        : null;

    $hasActiveFile = $activeScheduleImg && ! empty($activeScheduleImg->image_path) && file_exists(public_path($activeScheduleImg->image_path));
    $hasStaticFile = file_exists(public_path('uploads/jadwal/jadwal-shift-current.jpg'));

    $scheduleImgSrc = $hasActiveFile
        ? asset($activeScheduleImg->image_path).'?v='.($activeScheduleImg->updated_at?->timestamp ?? time())
        : ($hasStaticFile ? asset('uploads/jadwal/jadwal-shift-current.jpg').'?v='.filemtime(public_path('uploads/jadwal/jadwal-shift-current.jpg')) : null);

    $scheduleTitleText = $activeScheduleImg?->title ?? 'Jadwal Shift Operator & Kasir';
    $schedulePeriodText = $activeScheduleImg?->period ?? ($staticJadwalInfo['updated_at_formatted'] ?? null);
    $scheduleNotesText = $activeScheduleImg?->notes ?? null;

    $lastUpdatedTimestamp = $activeScheduleImg?->updated_at?->timestamp
        ?? ($hasStaticFile ? (int) filemtime(public_path('uploads/jadwal/jadwal-shift-current.jpg')) : null);

    $isRecentlyUpdatedSchedule = $lastUpdatedTimestamp ? ((time() - $lastUpdatedTimestamp) <= 172800) : false;

    $scheduleUpdatedTime = $activeScheduleImg?->updated_at
        ? $activeScheduleImg->updated_at->format('d M Y, H:i').' WIB'
        : ($staticJadwalInfo['updated_at_formatted']
            ?? ($lastUpdatedTimestamp ? date('d M Y, H:i', $lastUpdatedTimestamp).' WIB' : '-'));
@endphp

<div id="modal-jadwal-shift-terbaru" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(6px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;" onclick="if(event.target === this) closeJadwalShiftModal()">
    <div style="background: #0f172a; color: #f8fafc; width: 100%; max-width: 960px; max-height: 92vh; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.65); overflow: hidden; display: flex; flex-direction: column; border: 1px solid rgba(56, 189, 248, 0.35);">
        {{-- HEADER MODAL --}}
        <div style="background: linear-gradient(135deg, #0284c7, #1e3a8a); color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">📅</span>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #fff;">{{ $scheduleTitleText }}</h3>
                        @if($isRecentlyUpdatedSchedule)
                            <span style="background: #fef08a; color: #854d0e; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 999px; letter-spacing: 0.4px;">
                                ✨ BARU DIPERBARUI
                            </span>
                        @endif
                    </div>
                    <div style="font-size: 11.5px; color: #bae6fd; margin-top: 2px;">
                        @if($schedulePeriodText)
                            Periode: <strong>{{ $schedulePeriodText }}</strong> &bull;
                        @endif
                        Diperbarui: <strong>{{ $scheduleUpdatedTime }}</strong>
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                @if($scheduleImgSrc)
                    <button type="button" onclick="toggleZoomJadwalShiftImg()" id="btn-zoom-jadwal-shift" style="background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 6px 11px; border-radius: 8px; font-size: 11.5px; font-weight: 700; cursor: pointer;">
                        🔍 Perbesar (Zoom)
                    </button>
                    <a href="{{ $scheduleImgSrc }}" target="_blank" rel="noopener" style="background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 6px 11px; border-radius: 8px; font-size: 11.5px; font-weight: 700; text-decoration: none;">
                        ↗ Buka Tab Penuh
                    </a>
                @endif
                <button type="button" onclick="closeJadwalShiftModal()" style="background: rgba(239, 68, 68, 0.85); border: none; color: #fff; width: 30px; height: 30px; border-radius: 8px; cursor: pointer; font-weight: 800; font-size: 15px; line-height: 1;">&times;</button>
            </div>
        </div>

        {{-- CATATAN TAMBAHAN JIKA ADA --}}
        @if(!empty($scheduleNotesText))
            <div style="padding: 10px 18px; background: rgba(245, 158, 11, 0.14); border-bottom: 1px solid rgba(245, 158, 11, 0.3); color: #fde68a; font-size: 12px; display: flex; align-items: center; gap: 8px;">
                <span>📌</span>
                <div><strong>Catatan Admin:</strong> {{ $scheduleNotesText }}</div>
            </div>
        @endif

        {{-- BODY GAMBAR JADWAL --}}
        <div id="jadwal-shift-img-scroll-wrap" style="padding: 16px; overflow: auto; flex: 1; background: #020617; display: flex; align-items: flex-start; justify-content: center;">
            @if($scheduleImgSrc)
                <img id="jadwal-shift-modal-img"
                     src="{{ $scheduleImgSrc }}"
                     alt="{{ $scheduleTitleText }}"
                     onclick="toggleZoomJadwalShiftImg()"
                     title="Klik gambar untuk memperbesar / memperkecil"
                     style="max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); cursor: zoom-in; transition: width 0.2s ease;">
            @else
                <div style="text-align: center; padding: 48px 20px; color: #94a3b8;">
                    <div style="font-size: 40px; margin-bottom: 10px;">🗓️</div>
                    <div style="font-size: 15px; font-weight: 700; color: #e2e8f0;">Belum Ada Gambar Jadwal Shift Aktif</div>
                    <div style="font-size: 12.5px; margin-top: 4px;">Admin belum mengunggah jadwal shift terbaru di menu Kelola Jadwal Shift.</div>
                </div>
            @endif
        </div>

        {{-- FOOTER MODAL --}}
        <div style="padding: 11px 18px; background: #0f172a; border-top: 1px solid rgba(148, 163, 184, 0.2); display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="font-size: 11.5px; color: #94a3b8;">
                💡 <em>Tips: Klik pada gambar jadwal untuk memperbesar (Zoom In/Out) agar nama &amp; jam shift terlihat jelas.</em>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                @if($scheduleImgSrc)
                    <a href="{{ $scheduleImgSrc }}" download="Jadwal-Shift-Terbaru.jpg" style="padding: 7px 14px; background: #0284c7; color: #fff; border-radius: 7px; font-weight: 700; font-size: 12px; text-decoration: none;">
                        ⬇️ Simpan Gambar
                    </a>
                @endif
                <button type="button" onclick="closeJadwalShiftModal()" style="padding: 7px 16px; background: #334155; color: #f8fafc; border: 1px solid #475569; border-radius: 7px; font-weight: 700; font-size: 12px; cursor: pointer;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openJadwalShiftModal() {
    var modal = document.getElementById('modal-jadwal-shift-terbaru');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeJadwalShiftModal() {
    var modal = document.getElementById('modal-jadwal-shift-terbaru');
    if (modal) {
        modal.style.display = 'none';
    }
}

function toggleZoomJadwalShiftImg() {
    var img = document.getElementById('jadwal-shift-modal-img');
    var btn = document.getElementById('btn-zoom-jadwal-shift');
    if (!img) return;

    var isZoomed = img.getAttribute('data-zoomed') === '1';
    if (isZoomed) {
        img.setAttribute('data-zoomed', '0');
        img.style.maxWidth = '100%';
        img.style.width = 'auto';
        img.style.cursor = 'zoom-in';
        if (btn) btn.innerHTML = '🔍 Perbesar (Zoom)';
    } else {
        img.setAttribute('data-zoomed', '1');
        img.style.maxWidth = 'none';
        img.style.width = '165%';
        img.style.cursor = 'zoom-out';
        if (btn) btn.innerHTML = '🔎 Perkecil (Fit)';
    }
}
</script>
