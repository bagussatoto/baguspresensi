<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Supplier;

/**
 * Controller Admin ERP Maya Graha Kencana: Daftar Supplier & Manajemen Pemasok.
 */
class AdminSupplierController extends Controller
{
    /**
     * Tampilkan halaman Daftar Supplier ERP Maya Graha Kencana.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Supplier::query()->orderBy('id', 'asc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('city', 'ilike', "%{$search}%")
                    ->orWhere('city_note', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('contact_person', 'ilike', "%{$search}%")
                    ->orWhere('npwp', 'ilike', "%{$search}%")
                    ->orWhere('nik', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $suppliers = $query->get();
        $totalCount = $suppliers->count();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $totalCount,
                'suppliers' => $suppliers,
            ]);
        }

        return view('admin.supplier.index', compact('suppliers', 'totalCount'));
    }

    /**
     * Simpan data supplier baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'city_note' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = ! empty($validated['code'])
            ? trim((string) $validated['code'])
            : (string) ((Supplier::max('id') ?? 0) + 1);

        $supplier = Supplier::create([
            'code' => $code,
            'name' => trim($validated['name']),
            'city' => $validated['city'] ?? null,
            'city_note' => $validated['city_note'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'contact_person' => $validated['contact_person'] ?? '-',
            'npwp' => $validated['npwp'] ?? '-',
            'nik' => $validated['nik'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Supplier {$supplier->name} berhasil ditambahkan.",
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('admin.supplier')->with('success', "Supplier {$supplier->name} berhasil ditambahkan.");
    }

    /**
     * Update data supplier.
     */
    public function update(Request $request, Supplier $supplier): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:200'],
            'code' => ['sometimes', 'nullable', 'string', 'max:50'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city_note' => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'contact_person' => ['sometimes', 'nullable', 'string', 'max:100'],
            'npwp' => ['sometimes', 'nullable', 'string', 'max:50'],
            'nik' => ['sometimes', 'nullable', 'string', 'max:50'],
            'address' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $supplier->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Supplier {$supplier->name} berhasil diperbarui.",
                'supplier' => $supplier->fresh(),
            ]);
        }

        return redirect()->route('admin.supplier')->with('success', "Supplier {$supplier->name} berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / non-aktif supplier.
     */
    public function toggleStatus(Supplier $supplier): JsonResponse
    {
        $supplier->update([
            'is_active' => ! $supplier->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Status supplier {$supplier->name} diubah menjadi ".($supplier->is_active ? 'Aktif' : 'Non-Aktif'),
            'is_active' => $supplier->is_active,
        ]);
    }

    /**
     * Hapus supplier.
     */
    public function destroy(Request $request, Supplier $supplier): JsonResponse|RedirectResponse
    {
        $name = $supplier->name;
        $supplier->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Supplier {$name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.supplier')->with('success', "Supplier {$name} berhasil dihapus.");
    }
}
