<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NhaCungCapRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ten_ncc' => ['required', 'string', 'max:255'],
            'dia_chi' => ['nullable', 'string', 'max:255'],
            'ma_so_thue' => ['nullable', 'string', 'max:50'],
            'sdt' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'mo_ta' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'ten_ncc.required' => 'Vui lòng nhập tên nhà cung cấp.',
            'email.email' => 'Email không hợp lệ.',
        ];
    }
}
