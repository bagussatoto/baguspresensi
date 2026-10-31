<?php

declare(strict_types=1);

namespace Modules\Distribusi\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryOrderRequest extends FormRequest
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
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'delivery_date' => ['nullable', 'date'],
            'recipient_name' => ['nullable', 'string', 'max:100'],
            'shipping_address' => ['nullable', 'string'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }
}
