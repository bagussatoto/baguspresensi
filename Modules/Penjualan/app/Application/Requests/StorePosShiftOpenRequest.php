<?php

declare(strict_types=1);

namespace Modules\Penjualan\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosShiftOpenRequest extends FormRequest
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
            'kas_account_id' => ['required', 'exists:kas_accounts,id'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
