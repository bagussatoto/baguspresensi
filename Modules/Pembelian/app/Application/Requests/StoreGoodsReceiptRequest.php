<?php

declare(strict_types=1);

namespace Modules\Pembelian\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
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
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'delivery_note_number' => ['nullable', 'string', 'max:100'],
            'receipt_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'create_bill' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['nullable', 'exists:purchase_order_items,id'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.qty_received' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }
}
