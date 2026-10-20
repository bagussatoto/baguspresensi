<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Models\Customer;

/**
 * Controller Admin ERP Maya Graha Kencana: Manajemen Data Konsumen & Member Pelanggan.
 */
class AdminCustomerController extends Controller
{
    /**
     * Tampilkan halaman Daftar Konsumen & Member ERP MGK.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Customer::query()->orderBy('id', 'asc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('customer_type', $request->input('type'));
        }

        $customers = $query->get();
        $totalCount = Customer::count();
        $customerTypes = CustomerType::cases();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $customers->count(),
                'customers' => $customers,
            ]);
        }

        return view('admin.konsumen.index', compact('customers', 'totalCount', 'customerTypes'));
    }

    /**
     * Simpan konsumen baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50'],
            'customer_type' => ['required', 'string', 'in:retail,grosir,reseller,proyek'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:300'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_term_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = ! empty($validated['code'])
            ? trim((string) $validated['code'])
            : 'CUST-'.strtoupper(Str::random(5));

        $customer = Customer::create([
            'code' => $code,
            'name' => trim($validated['name']),
            'customer_type' => $validated['customer_type'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'credit_limit' => (float) ($validated['credit_limit'] ?? 0),
            'current_credit_used' => 0.0,
            'payment_term_days' => (int) ($validated['payment_term_days'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Konsumen {$customer->name} berhasil didaftarkan.",
                'customer' => $customer,
            ], 201);
        }

        return redirect()->route('admin.konsumen')->with('success', "Konsumen {$customer->name} berhasil didaftarkan.");
    }

    /**
     * Update data konsumen.
     */
    public function update(Request $request, Customer $customer): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'code' => ['sometimes', 'string', 'max:50'],
            'customer_type' => ['sometimes', 'string', 'in:retail,grosir,reseller,proyek'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:300'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_term_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $customer->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data konsumen {$customer->name} berhasil diperbarui.",
                'customer' => $customer->fresh(),
            ]);
        }

        return redirect()->route('admin.konsumen')->with('success', "Data konsumen {$customer->name} berhasil diperbarui.");
    }

    /**
     * Toggle status aktif.
     */
    public function toggleStatus(Customer $customer): JsonResponse|RedirectResponse
    {
        $customer->update(['is_active' => ! $customer->is_active]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Status {$customer->name} berhasil diubah menjadi ".($customer->is_active ? 'Aktif' : 'Non-Aktif'),
                'is_active' => $customer->is_active,
            ]);
        }

        return redirect()->route('admin.konsumen');
    }

    /**
     * Hapus konsumen.
     */
    public function destroy(Customer $customer): JsonResponse|RedirectResponse
    {
        $name = $customer->name;
        $customer->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Konsumen {$name} berhasil dihapus dari sistem.",
            ]);
        }

        return redirect()->route('admin.konsumen')->with('success', "Konsumen {$name} berhasil dihapus.");
    }
}
