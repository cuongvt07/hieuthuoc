<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GiaThuocRequest extends FormRequest
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
            'thuoc_id' => ['required', 'exists:thuoc,thuoc_id'],
            'gia_ban' => ['required', 'numeric', 'min:0'],
            'ngay_bat_dau' => ['required', 'date'],
            'ngay_ket_thuc' => ['nullable', 'date', 'after_or_equal:ngay_bat_dau'],
        ];
        
        // Nếu là tạo mới thì kiểm tra thuốc không được trùng
        if ($this->isMethod('POST')) {
            $rules['thuoc_id'][] = 'unique:gia_thuoc,thuoc_id';
        }
        
        return $rules;
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'thuoc_id.required' => 'Vui lòng chọn thuốc.',
            'thuoc_id.exists' => 'Thuốc không tồn tại.',
            'thuoc_id.unique' => 'Thuốc này đã có giá. Vui lòng chỉnh sửa giá hiện có.',
            'gia_ban.required' => 'Vui lòng nhập giá bán.',
            'gia_ban.numeric' => 'Giá bán phải là số.',
            'gia_ban.min' => 'Giá bán phải lớn hơn hoặc bằng 0.',
            'ngay_bat_dau.required' => 'Vui lòng nhập ngày bắt đầu.',
            'ngay_bat_dau.date' => 'Ngày bắt đầu không hợp lệ.',
            'ngay_ket_thuc.date' => 'Ngày kết thúc không hợp lệ.',
            'ngay_ket_thuc.after_or_equal' => 'Ngày kết thúc phải sau hoặc cùng ngày bắt đầu.',
        ];
    }
}
