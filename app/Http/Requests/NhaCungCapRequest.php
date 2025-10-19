<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

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
        $ncc = $this->route('nha_cung_cap');
        $nccId = is_object($ncc) ? $ncc->ncc_id : $ncc;
        return [
            'ma_so_thue' => [
                'nullable',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('nha_cung_cap', 'ma_so_thue')->ignore($nccId, 'ncc_id'),
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'ma_so_thue.unique' => 'Mã số thuế đã tồn tại.',
        ];
    }
}
