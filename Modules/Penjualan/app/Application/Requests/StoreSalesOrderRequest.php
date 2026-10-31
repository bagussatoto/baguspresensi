<?php

declare(strict_types=1);

namespace Modules\Penjualan\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['nullable', 'date'],
            'payment_term_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
