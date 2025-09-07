<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KhachHangRequest extends FormRequest
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
        $rules = [
            'ho_ten' => ['required', 'string', 'max:255'],
        ];

        // Only validate uniqueness of sdt when creating or updating with a different sdt
        if ($this->isMethod('post')) {
            $rules['sdt'] = ['required', 'string', 'max:20', 'unique:khach_hang,sdt'];
        } else {
            $rules['sdt'] = [
                'required', 
                'string', 
                'max:20', 
                Rule::unique('khach_hang', 'sdt')->ignore($this->route('khach_hang')->khach_hang_id, 'khach_hang_id')
            ];
        }

        return $rules;
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'sdt.required' => 'Vui lòng nhập số điện thoại.',
            'sdt.unique' => 'Số điện thoại đã tồn tại.',
            'ho_ten.required' => 'Vui lòng nhập họ tên khách hàng.',
        ];
    }
}
