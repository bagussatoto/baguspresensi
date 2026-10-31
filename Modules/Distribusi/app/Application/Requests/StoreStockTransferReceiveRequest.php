<?php

declare(strict_types=1);

namespace Modules\Distribusi\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferReceiveRequest extends FormRequest
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
            'received_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.qty_received' => ['required', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }
}
