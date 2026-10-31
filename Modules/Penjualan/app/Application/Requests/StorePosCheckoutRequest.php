<?php

declare(strict_types=1);

namespace Modules\Penjualan\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\Kas\Domain\Enums\PaymentMethodType;

class StorePosCheckoutRequest extends FormRequest
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
            'branch_id' => ['required', 'exists:branches,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'pos_shift_id' => ['nullable', 'exists:pos_shifts,id'],
            'payment_method' => ['required', new Enum(PaymentMethodType::class)],
            'cash_paid' => ['nullable', 'numeric', 'min:0'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
