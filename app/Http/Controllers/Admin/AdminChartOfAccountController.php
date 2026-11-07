<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\MasterData\Domain\Models\Branch;

/**
 * Controller Admin ERP Maya Graha Kencana: Bagan Akun / Chart of Accounts (COA).
 */
class AdminChartOfAccountController extends Controller
{
    /**
     * Tampilkan halaman Chart of Accounts (COA) berjenjang.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = ChartOfAccount::with(['parent', 'children', 'branch'])
            ->orderBy('account_code', 'asc');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('account_code', 'ilike', "%{$search}%")
                    ->orWhere('account_name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $cat = (string) $request->input('category');
            if ($cat !== 'all') {
                $query->where('account_category', $cat);
            }
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $accounts = $query->get();
        $totalCount = ChartOfAccount::count();
        $parentOptions = ChartOfAccount::where('is_header', true)->orderBy('account_code')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $categorySummary = [];
        foreach (AccountCategory::cases() as $case) {
            $categorySummary[$case->value] = [
                'label' => $case->label(),
                'count' => ChartOfAccount::where('account_category', $case->value)->count(),
                'total_balance' => (float) ChartOfAccount::where('account_category', $case->value)->where('is_header', false)->sum('current_balance'),
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $accounts->count(),
                'accounts' => $accounts,
                'categories' => $categorySummary,
            ]);
        }

        return view('admin.coa.index', compact('accounts', 'totalCount', 'parentOptions', 'branches', 'categorySummary'));
    }

    /**
     * Simpan akun COA baru.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'account_code' => ['required', 'string', 'max:50', 'unique:chart_of_accounts,account_code'],
            'account_name' => ['required', 'string', 'max:150'],
            'account_category' => ['required', Rule::enum(AccountCategory::class)],
            'normal_balance' => ['required', Rule::enum(NormalBalance::class)],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_header' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $isHeader = $request->boolean('is_header');
        $parentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;

        $level = 1;
        if ($parentId) {
            $parent = ChartOfAccount::find($parentId);
            $level = $parent ? min(4, $parent->level + 1) : 2;
        } elseif (! $isHeader) {
            $level = 3;
        }

        $coa = ChartOfAccount::create([
            'account_code' => trim((string) $request->input('account_code')),
            'account_name' => trim((string) $request->input('account_name')),
            'account_category' => $request->input('account_category'),
            'normal_balance' => $request->input('normal_balance'),
            'parent_id' => $parentId,
            'branch_id' => $request->filled('branch_id') ? (int) $request->input('branch_id') : null,
            'level' => $level,
            'is_header' => $isHeader,
            'is_active' => true,
            'description' => $request->input('description'),
            'current_balance' => 0.00,
        ]);

        $message = "Akun {$coa->account_code} - {$coa->account_name} berhasil ditambahkan ke Chart of Accounts!";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $coa,
            ], 201);
        }

        return redirect()->route('admin.coa')->with('success', $message);
    }

    /**
     * Update data akun COA.
     */
    public function update(Request $request, ChartOfAccount $coa): RedirectResponse|JsonResponse
    {
        $request->validate([
            'account_code' => ['required', 'string', 'max:50', Rule::unique('chart_of_accounts', 'account_code')->ignore($coa->id)],
            'account_name' => ['required', 'string', 'max:150'],
            'account_category' => ['required', Rule::enum(AccountCategory::class)],
            'normal_balance' => ['required', Rule::enum(NormalBalance::class)],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_header' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $isHeader = $request->boolean('is_header');
        $parentId = $request->filled('parent_id') ? (int) $request->input('parent_id') : null;

        $level = 1;
        if ($parentId) {
            $parent = ChartOfAccount::find($parentId);
            $level = $parent ? min(4, $parent->level + 1) : 2;
        } elseif (! $isHeader) {
            $level = 3;
        }

        $coa->update([
            'account_code' => trim((string) $request->input('account_code')),
            'account_name' => trim((string) $request->input('account_name')),
            'account_category' => $request->input('account_category'),
            'normal_balance' => $request->input('normal_balance'),
            'parent_id' => $parentId,
            'branch_id' => $request->filled('branch_id') ? (int) $request->input('branch_id') : null,
            'level' => $level,
            'is_header' => $isHeader,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $coa->is_active,
            'description' => $request->input('description'),
        ]);

        $message = "Akun {$coa->account_code} - {$coa->account_name} berhasil diperbarui!";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $coa,
            ]);
        }

        return redirect()->route('admin.coa')->with('success', $message);
    }

    /**
     * Toggle status aktif/nonaktif akun COA.
     */
    public function toggleStatus(ChartOfAccount $coa): JsonResponse|RedirectResponse
    {
        $coa->is_active = ! $coa->is_active;
        $coa->save();

        $statusStr = $coa->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Akun {$coa->account_code} berhasil {$statusStr}.";

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $coa->is_active,
            ]);
        }

        return redirect()->route('admin.coa')->with('success', $message);
    }

    /**
     * Hapus akun COA jika belum memiliki transaksi.
     */
    public function destroy(ChartOfAccount $coa): JsonResponse|RedirectResponse
    {
        if ($coa->children()->count() > 0) {
            $errMsg = "Akun {$coa->account_code} tidak dapat dihapus karena masih memiliki sub-akun anak.";
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return redirect()->route('admin.coa')->with('error', $errMsg);
        }

        if ($coa->kasAccounts()->count() > 0) {
            $errMsg = "Akun {$coa->account_code} terhubung dengan Akun Kas & Bank dan tidak dapat dihapus.";
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return redirect()->route('admin.coa')->with('error', $errMsg);
        }

        $code = $coa->account_code;
        $coa->delete();

        $message = "Akun {$code} berhasil dihapus dari Chart of Accounts.";

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('admin.coa')->with('success', $message);
    }
}
