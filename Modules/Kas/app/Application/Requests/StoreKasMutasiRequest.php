<?php

declare(strict_types=1);

namespace Modules\Kas\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKasMutasiRequest extends FormRequest
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
            'from_kas_account_id' => ['required', 'exists:kas_accounts,id', 'different:to_kas_account_id'],
            'to_kas_account_id' => ['required', 'exists:kas_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'authorized_by' => ['nullable', 'exists:users,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'transfer_date' => ['nullable', 'date'],
        ];
    }
}
