<?php

declare(strict_types=1);

namespace Modules\MasterData\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Enums\TaxIdType;

class StoreCustomerRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50', 'unique:customers,code'],
            'name' => ['required', 'string', 'max:200'],
            'customer_type' => ['required', new Enum(CustomerType::class)],
            'pricelist_id' => ['nullable', 'exists:pricelists,id'],
            'tax_id_type' => ['required', new Enum(TaxIdType::class)],
            'tax_id_number' => [
                'nullable',
                'string',
                'max:30',
                function ($attribute, $value, $fail) {
                    $taxType = TaxIdType::tryFrom((string) $this->input('tax_id_type'));
                    if ($taxType && in_array($taxType, [TaxIdType::NPWP_16, TaxIdType::NIK], true)) {
                        if (! $taxType->isValidNumber($value)) {
                            $fail("Nomor {$taxType->label()} harus berjumlah tepat 16 digit angka sesuai ketentuan DJP Coretax.");
                        }
                    }
                },
            ],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_term_days' => ['nullable', 'integer', 'min:0'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
        ];
    }
}
