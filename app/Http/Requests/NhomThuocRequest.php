<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NhomThuocRequest extends FormRequest
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
            'ten_nhom' => ['required', 'string', 'max:255'],
            'mo_ta' => ['nullable', 'string'],
            'trang_thai' => ['nullable', 'default:1', 'in:0,1']
        ];

        // Only validate uniqueness of ma_nhom when creating or updating with a different ma_nhom
        if ($this->isMethod('post')) {
            $rules['ma_nhom'] = ['required', 'string', 'max:50', 'unique:nhom_thuoc,ma_nhom'];
        } else {
            $rules['ma_nhom'] = [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('nhom_thuoc', 'ma_nhom')->ignore($this->route('nhom_thuoc')->nhom_id, 'nhom_id')
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
            'ma_nhom.required' => 'Vui lòng nhập mã nhóm thuốc.',
            'ma_nhom.unique' => 'Mã nhóm thuốc đã tồn tại.',
            'ten_nhom.required' => 'Vui lòng nhập tên nhóm thuốc.',
        ];
    }
}
