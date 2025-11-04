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

            // Route parameter may be named 'nhom_thuoc' (snake) or 'nhomThuoc' (camel) or 'id'.
            $routeParam = $this->route('nhom_thuoc') ?? $this->route('nhomThuoc') ?? $this->route('id');
            $ignoreId = null;
            if (is_object($routeParam) && isset($routeParam->nhom_id)) {
                $ignoreId = $routeParam->nhom_id;
            } elseif (is_numeric($routeParam)) {
                $ignoreId = $routeParam;
            }

            $uniqueRule = Rule::unique('nhom_thuoc', 'ma_nhom');
            if ($ignoreId) {
                $uniqueRule = $uniqueRule->ignore($ignoreId, 'nhom_id');
            }

            $rules['ma_nhom'] = [
                'required', 
                'string', 
                'max:50', 
                $uniqueRule
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
