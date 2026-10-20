<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftScheduleImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Admin ERP Maya Graha Kencana: Jadwal Shift Operator (Gambar).
 */
class AdminScheduleController extends Controller
{
    /**
     * Tampilkan halaman Jadwal Shift Operator berbasis input gambar.
     */
    public function index(Request $request): View|JsonResponse
    {
        $activeSchedule = ShiftScheduleImage::where('is_active', true)->latest()->first()
            ?? ShiftScheduleImage::latest()->first();

        $historySchedules = ShiftScheduleImage::orderBy('id', 'desc')->get();

        $staticInfo = null;
        if (file_exists(public_path('uploads/jadwal/info.json'))) {
            $staticInfo = json_decode((string) file_get_contents(public_path('uploads/jadwal/info.json')), true);
        }
        $staticModified = file_exists(public_path('uploads/jadwal/jadwal-shift-current.jpg'))
            ? filemtime(public_path('uploads/jadwal/jadwal-shift-current.jpg'))
            : null;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'active_schedule' => $activeSchedule,
                'history_schedules' => $historySchedules,
                'static_info' => $staticInfo,
            ]);
        }

        return view('admin.jadwal.index', compact('activeSchedule', 'historySchedules', 'staticInfo', 'staticModified'));
    }

    /**
     * Upload & simpan gambar jadwal shift baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'], // Maks 10MB
            'title' => ['nullable', 'string', 'max:200'],
            'period' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'jadwal-shift-'.date('Ymd_His').'-'.bin2hex(random_bytes(4)).'.'.$extension;

        $destinationPath = public_path('uploads/jadwal');
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file->move($destinationPath, $filename);
        $relativePath = 'uploads/jadwal/'.$filename;

        // Salin juga sebagai gambar aktif standar
        copy($destinationPath.'/'.$filename, $destinationPath.'/jadwal-shift-current.jpg');

        // Nonaktifkan jadwal sebelumnya jika ada
        ShiftScheduleImage::where('is_active', true)->update(['is_active' => false]);

        $title = $request->filled('title')
            ? trim((string) $request->input('title'))
            : 'Jadwal Shift '.date('d M Y');

        $schedule = ShiftScheduleImage::create([
            'title' => $title,
            'image_path' => $relativePath,
            'period' => $request->input('period'),
            'notes' => $request->input('notes'),
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar jadwal shift berhasil diunggah & diperbarui.',
                'schedule' => $schedule,
            ]);
        }

        return redirect()->route('admin.jadwal')->with('success', 'Gambar jadwal shift berhasil diunggah.');
    }

    /**
     * Unggah & timpa langsung gambar jadwal statis tanpa database.
     */
    public function uploadStatic(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $destinationPath = public_path('uploads/jadwal');
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file = $request->file('image');
        $filename = 'jadwal-shift-current.jpg';

        $file->move($destinationPath, $filename);

        $meta = [
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_at_formatted' => date('d M Y, H:i').' WIB',
            'filesize_kb' => round(filesize($destinationPath.'/'.$filename) / 1024, 1),
            'filename' => $filename,
        ];
        file_put_contents($destinationPath.'/info.json', json_encode($meta, JSON_PRETTY_PRINT));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar jadwal shift statis berhasil diperbarui.',
                'info' => $meta,
                'url' => asset('uploads/jadwal/'.$filename).'?v='.time(),
            ]);
        }

        return redirect()->route('admin.jadwal')->with('success', 'Gambar jadwal shift statis berhasil diperbarui (tanpa database).');
    }

    /**
     * Set gambar jadwal tertentu sebagai jadwal aktif.
     */
    public function setActive(ShiftScheduleImage $schedule): JsonResponse|RedirectResponse
    {
        ShiftScheduleImage::where('is_active', true)->update(['is_active' => false]);
        $schedule->update(['is_active' => true]);

        $sourceFile = public_path($schedule->image_path);
        $currentFile = public_path('uploads/jadwal/jadwal-shift-current.jpg');

        if (file_exists($sourceFile)) {
            copy($sourceFile, $currentFile);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Jadwal '{$schedule->title}' sekarang aktif.",
                'schedule' => $schedule->fresh(),
            ]);
        }

        return redirect()->route('admin.jadwal')->with('success', "Jadwal '{$schedule->title}' sekarang aktif.");
    }

    /**
     * Hapus arsip gambar jadwal.
     */
    public function destroy(ShiftScheduleImage $schedule): JsonResponse|RedirectResponse
    {
        $filePath = public_path($schedule->image_path);
        if (file_exists($filePath) && str_contains($schedule->image_path, 'jadwal-shift-20')) {
            @unlink($filePath);
        }

        $schedule->delete();

        // Jika jadwal aktif dihapus, jadikan yang terakhir sebagai aktif
        if ($schedule->is_active) {
            $latest = ShiftScheduleImage::latest()->first();
            if ($latest) {
                $latest->update(['is_active' => true]);
                if (file_exists(public_path($latest->image_path))) {
                    copy(public_path($latest->image_path), public_path('uploads/jadwal/jadwal-shift-current.jpg'));
                }
            }
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gambar jadwal shift berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.jadwal')->with('success', 'Gambar jadwal shift berhasil dihapus.');
    }
}
