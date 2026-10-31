<?php

declare(strict_types=1);

namespace Modules\Penjualan\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClosePosShiftRequest extends FormRequest
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
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
