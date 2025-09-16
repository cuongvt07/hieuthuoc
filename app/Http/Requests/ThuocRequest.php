<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThuocRequest extends FormRequest
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
            'nhom_id' => ['required', 'exists:nhom_thuoc,nhom_id'],
            'ten_thuoc' => ['required', 'string', 'max:255'],
            'mo_ta' => ['nullable', 'string'],
            'don_vi_goc' => ['required', 'string', 'max:50'],
            'don_vi_ban' => ['required', 'string', 'max:50'],
            'ti_le_quy_doi' => ['required', 'numeric', 'min:0.01'],
            'trang_thai' => ['nullable', 'default:1', 'in:0,1']
        ];

        // Only validate uniqueness of ma_thuoc when creating or updating with a different ma_thuoc
        if ($this->isMethod('post')) {
            $rules['ma_thuoc'] = ['required', 'string', 'max:50', 'unique:thuoc,ma_thuoc'];
        } else {
            $rules['ma_thuoc'] = [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('thuoc', 'ma_thuoc')->ignore($this->route('thuoc')->thuoc_id, 'thuoc_id')
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
            'nhom_id.required' => 'Vui lòng chọn nhóm thuốc.',
            'nhom_id.exists' => 'Nhóm thuốc không tồn tại.',
            'ma_thuoc.required' => 'Vui lòng nhập mã thuốc.',
            'ma_thuoc.unique' => 'Mã thuốc đã tồn tại.',
            'ten_thuoc.required' => 'Vui lòng nhập tên thuốc.',
            'don_vi_goc.required' => 'Vui lòng nhập đơn vị gốc.',
            'don_vi_ban.required' => 'Vui lòng nhập đơn vị bán.',
            'ti_le_quy_doi.required' => 'Vui lòng nhập tỉ lệ quy đổi.',
            'ti_le_quy_doi.numeric' => 'Tỉ lệ quy đổi phải là số.',
            'ti_le_quy_doi.min' => 'Tỉ lệ quy đổi phải lớn hơn 0.',
        ];
    }
}
