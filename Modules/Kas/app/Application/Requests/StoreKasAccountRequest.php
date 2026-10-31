<?php

declare(strict_types=1);

namespace Modules\Kas\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\Kas\Domain\Enums\KasAccountType;

class StoreKasAccountRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50', 'unique:kas_accounts,code'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', new Enum(KasAccountType::class)],
            'current_balance' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
