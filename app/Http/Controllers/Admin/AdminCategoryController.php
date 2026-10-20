<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Category;

/**
 * Controller Admin ERP Maya Graha Kencana: Daftar Kategori Produk & Manajemen Hierarki.
 */
class AdminCategoryController extends Controller
{
    /**
     * Tampilkan halaman Daftar Kategori ERP Maya Graha Kencana.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Category::with(['parent', 'children'])
            ->withCount('products')
            ->orderByRaw('LOWER(name) ASC');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('parent_id')) {
            if ($request->input('parent_id') === 'root') {
                $query->whereNull('parent_id');
            } elseif ($request->input('parent_id') !== 'all') {
                $query->where('parent_id', (int) $request->input('parent_id'));
            }
        }

        $categories = $query->get();
        $totalCount = Category::count();
        $parentCategories = Category::whereNull('parent_id')->orderBy('name', 'asc')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $categories->count(),
                'categories' => $categories,
            ]);
        }

        return view('admin.kategori.index', compact('categories', 'totalCount', 'parentCategories'));
    }

    /**
     * Simpan data kategori baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $name = trim($validated['name']);
        $code = ! empty($validated['code']) ? trim((string) $validated['code']) : null;
        $parentId = ! empty($validated['parent_id']) ? (int) $validated['parent_id'] : null;
        $isActive = $request->boolean('is_active', true);

        // Otomatis buat slug yang unik
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'kategori';
        }
        $slug = $baseSlug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        if (empty($code)) {
            $code = 'CAT-'.strtoupper(substr($baseSlug, 0, 4)).'-'.rand(100, 999);
        }

        $category = Category::create([
            'name' => $name,
            'code' => $code,
            'slug' => $slug,
            'parent_id' => $parentId,
            'is_active' => $isActive,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori {$category->name} berhasil ditambahkan.",
                'category' => $category->load('parent')->loadCount('products'),
            ]);
        }

        return redirect()->route('admin.kategori')->with('success', "Kategori {$category->name} berhasil ditambahkan.");
    }

    /**
     * Update data kategori yang ada.
     */
    public function update(Request $request, Category $category): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id', 'not_in:'.$category->id],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['name'])) {
            $name = trim($validated['name']);
            $validated['name'] = $name;

            // Update slug jika nama berubah
            if ($name !== $category->name) {
                $baseSlug = Str::slug($name);
                if (empty($baseSlug)) {
                    $baseSlug = 'kategori';
                }
                $slug = $baseSlug;
                $counter = 1;
                while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $validated['slug'] = $slug;
            }
        }

        if (array_key_exists('parent_id', $validated) && empty($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }

        $category->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data kategori {$category->name} berhasil diperbarui.",
                'category' => $category->fresh()->load('parent')->loadCount('products'),
            ]);
        }

        return redirect()->route('admin.kategori')->with('success', "Data kategori {$category->name} berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/non-aktif kategori.
     */
    public function toggleStatus(Category $category): JsonResponse|RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status {$category->name} berhasil diubah menjadi ".($category->is_active ? 'Aktif' : 'Non-Aktif'),
                'is_active' => $category->is_active,
            ]);
        }

        return redirect()->route('admin.kategori');
    }

    /**
     * Hapus kategori dari sistem.
     */
    public function destroy(Category $category): JsonResponse|RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori {$name} berhasil dihapus dari sistem.",
            ]);
        }

        return redirect()->route('admin.kategori')->with('success', "Kategori {$name} berhasil dihapus.");
    }
}
