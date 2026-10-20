<?php

declare(strict_types=1);

namespace Modules\MasterData\Application\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\MasterData\Domain\Enums\CustomerType;

class StorePricelistRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50', 'unique:pricelists,code'],
            'name' => ['required', 'string', 'max:150'],
            'customer_type' => ['nullable', new Enum(CustomerType::class)],
            'currency' => ['nullable', 'string', 'max:10'],
        ];
    }
}
