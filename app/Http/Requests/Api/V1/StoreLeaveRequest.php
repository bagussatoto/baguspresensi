<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Rules\NoOverlappingLeaveRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi payload pengajuan izin / cuti karyawan.
 */
class StoreLeaveRequest extends FormRequest
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
            'leave_type' => ['required', 'string', 'in:annual,sick,permission,emergency'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', new NoOverlappingLeaveRule],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,pdf,jpg', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'ID Karyawan wajib diisi.',
            'employee_id.exists' => 'Karyawan tidak terdaftar di sistem.',
            'leave_type.required' => 'Jenis permohonan cuti/izin wajib dipilih.',
            'leave_type.in' => 'Jenis cuti harus salah satu dari: annual, sick, permission, emergency.',
            'start_date.required' => 'Tanggal awal permohonan wajib diisi.',
            'end_date.required' => 'Tanggal akhir permohonan wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
            'reason.required' => 'Alasan permohonan wajib diisi.',
        ];
    }
}
