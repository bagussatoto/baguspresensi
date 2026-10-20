<?php

declare(strict_types=1);

namespace Modules\MasterData\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Brand;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\Pricelist;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\Supplier;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;
use Modules\MasterData\Domain\Models\Warehouse;
use Modules\MasterData\Domain\Services\CustomerCreditGuard;
use Modules\MasterData\Domain\Services\PriceResolverEngine;
use Modules\MasterData\Domain\Services\PricingEngine;

class MasterDataWebController extends Controller
{
    public function __construct(
        private readonly PricingEngine $pricingEngine,
        private readonly PriceResolverEngine $priceResolver,
        private readonly CustomerCreditGuard $creditGuard,
    ) {}

    public function index(): View
    {
        $metrics = [
            'total_products' => Product::count(),
            'total_variants' => ProductVariant::count(),
            'total_customers' => Customer::count(),
            'total_suppliers' => Supplier::count(),
            'total_branches' => Branch::count(),
            'total_warehouses' => Warehouse::count(),
            'total_credit_limit' => Customer::sum('credit_limit'),
            'total_credit_used' => Customer::sum('current_credit_used'),
        ];

        $variants = ProductVariant::with(['product.category', 'product.brand', 'product.uom', 'attributeValues'])
            ->orderBy('id', 'asc')
            ->get();

        $pricelists = Pricelist::with(['items.variant'])->get();
        $customers = Customer::with('pricelist')->orderBy('id', 'asc')->get();
        $suppliers = Supplier::orderBy('id', 'asc')->get();
        $branches = Branch::with('warehouses')->orderBy('id', 'asc')->get();
        $warehouses = Warehouse::with('branch')->orderBy('id', 'asc')->get();
        $employees = Employee::with('branch')->orderBy('id', 'asc')->get();
        $categories = Category::all();
        $brands = Brand::all();
        $uoms = UnitOfMeasurement::all();

        return view('master-data.index', compact(
            'metrics',
            'variants',
            'pricelists',
            'customers',
            'suppliers',
            'branches',
            'warehouses',
            'employees',
            'categories',
            'brands',
            'uoms'
        ));
    }

    /**
     * AJAX Live Simulator: Resolusi Harga, Diskon Bertingkat, dan PPN 11% Coretax.
     */
    public function simulatePricing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'exists:product_variants,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'quantity' => ['required', 'numeric', 'min:1'],
            'discounts' => ['nullable', 'string'],
            'is_taxable' => ['nullable', 'boolean'],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);
        $customer = ! empty($validated['customer_id']) ? Customer::find($validated['customer_id']) : null;
        $quantity = (float) $validated['quantity'];
        $discountInput = $validated['discounts'] ?? '0';
        $isTaxable = filter_var($validated['is_taxable'] ?? true, FILTER_VALIDATE_BOOLEAN);

        // 1. Resolusi Harga menggunakan PriceResolverEngine jika ada Customer
        $unitPrice = (float) $variant->base_price;
        $resolvedSource = 'variant_base_price';
        $pricelistDiscount = 0.0;

        if ($customer) {
            $resolved = $this->priceResolver->resolve($variant, $customer, (int) $quantity);
            $unitPrice = $resolved->unitPrice;
            $resolvedSource = $resolved->source;
            $pricelistDiscount = $resolved->discountPercent;
        }

        // Jika ada diskon dari pricelist item dan user tidak menginput diskon kustom, gunakan diskon pricelist
        $activeDiscount = trim((string) $discountInput) !== '' && $discountInput !== '0'
            ? $discountInput
            : (float) $pricelistDiscount;

        // 2. Hitung dengan PricingEngine (termasuk floor PPN 11% DJP Coretax)
        $calc = $this->pricingEngine->calculate(
            unitPrice: $unitPrice,
            quantity: $quantity,
            discounts: $activeDiscount,
            isTaxable: $isTaxable
        );

        // 3. Cek Status Kelayakan Kredit jika ada Customer
        $creditStatus = null;
        if ($customer) {
            $canProceed = $this->creditGuard->canProceed($customer, $calc->netAmount);
            $creditStatus = [
                'can_proceed' => $canProceed,
                'credit_limit' => (float) $customer->credit_limit,
                'current_used' => (float) $customer->current_credit_used,
                'remaining' => $customer->remainingCreditLimit(),
                'after_transaction' => (float) $customer->current_credit_used + $calc->netAmount,
            ];
        }

        return response()->json([
            'resolved_unit_price' => $unitPrice,
            'resolved_source' => $resolvedSource,
            'calculation' => $calc->toArray(),
            'credit_status' => $creditStatus,
        ]);
    }
}
