<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Brand;

/**
 * Controller Admin ERP Maya Graha Kencana: Daftar Merk & Manajemen Brand.
 */
class AdminBrandController extends Controller
{
    /**
     * Tampilkan halaman Daftar Merk ERP Maya Graha Kencana.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        if (! Auth::check() && ! session()->has('admin_role') && ! app()->environment('testing')) {
            return redirect()->route('admin.login');
        }

        $query = Brand::query()->orderByRaw('LOWER(name) ASC');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $brands = $query->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $brands->count(),
                'brands' => $brands,
            ]);
        }

        return view('admin.merk.index', compact('brands'));
    }

    /**
     * Simpan data merk baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = trim($validated['name']);
        $code = ! empty($validated['code']) ? trim((string) $validated['code']) : null;
        $isActive = $request->boolean('is_active', true);

        // Generate unique slug
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'brand';
        }
        $slug = $baseSlug;
        $counter = 1;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $brand = Brand::create([
            'name' => $name,
            'code' => $code,
            'slug' => $slug,
            'is_active' => $isActive,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Merk {$brand->name} berhasil ditambahkan.",
                'brand' => $brand,
            ]);
        }

        return redirect()->route('admin.merk')->with('success', "Merk {$brand->name} berhasil ditambahkan.");
    }

    /**
     * Update data merk.
     */
    public function update(Request $request, Brand $brand): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'code' => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['name'])) {
            $validated['name'] = trim((string) $validated['name']);
            if (strtolower($validated['name']) !== strtolower($brand->name)) {
                $baseSlug = Str::slug($validated['name']);
                if (empty($baseSlug)) {
                    $baseSlug = 'brand';
                }
                $slug = $baseSlug;
                $counter = 1;
                while (Brand::where('slug', $slug)->where('id', '!=', $brand->id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $validated['slug'] = $slug;
            }
        }

        if (array_key_exists('code', $validated)) {
            $validated['code'] = ! empty($validated['code']) ? trim((string) $validated['code']) : null;
        }

        $brand->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Merk {$brand->name} berhasil diperbarui.",
                'brand' => $brand->fresh(),
            ]);
        }

        return redirect()->route('admin.merk')->with('success', "Merk {$brand->name} berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / non-aktif merk.
     */
    public function toggleStatus(Brand $brand): JsonResponse
    {
        $brand->update([
            'is_active' => ! $brand->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Status merk {$brand->name} diubah menjadi ".($brand->is_active ? 'Aktif' : 'Non-Aktif'),
            'is_active' => $brand->is_active,
        ]);
    }

    /**
     * Hapus merk.
     */
    public function destroy(Request $request, Brand $brand): JsonResponse|RedirectResponse
    {
        $name = $brand->name;
        $brand->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Merk {$name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.merk')->with('success', "Merk {$name} berhasil dihapus.");
    }
}
