<?php

declare(strict_types=1);

namespace Modules\Kas\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\Kas\Domain\Enums\PaymentMethodType;

class StoreKasMasukRequest extends FormRequest
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
            'kas_account_id' => ['required', 'exists:kas_accounts,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'payment_method' => ['required', new Enum(PaymentMethodType::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'transaction_date' => ['nullable', 'date'],
            'lines' => ['nullable', 'array'],
            'lines.*.reference_type' => ['nullable', 'string', 'max:100'],
            'lines.*.reference_id' => ['nullable', 'integer'],
            'lines.*.allocation_amount' => ['required_with:lines', 'numeric', 'min:0.01'],
            'lines.*.notes' => ['nullable', 'string'],
            'snapshot_payload' => ['nullable', 'array'],
        ];
    }
}
