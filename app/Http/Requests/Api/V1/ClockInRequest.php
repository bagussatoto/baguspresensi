<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi payload request presensi masuk (Clock In).
 */
class ClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['nullable'],
            'is_mock_location' => ['nullable', 'boolean'],
            'device_info' => ['nullable', 'array'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan validasi Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'ID Karyawan wajib diisi.',
            'employee_id.exists' => 'Karyawan tidak terdaftar di sistem.',
            'branch_id.required' => 'ID Cabang kantor wajib diisi.',
            'branch_id.exists' => 'Cabang kantor tidak ditemukan.',
            'latitude.required' => 'Titik koordinat latitude wajib dikirim.',
            'latitude.between' => 'Latitude harus berada di rentang -90 s/d 90.',
            'longitude.required' => 'Titik koordinat longitude wajib dikirim.',
            'longitude.between' => 'Longitude harus berada di rentang -180 s/d 180.',
        ];
    }
}
