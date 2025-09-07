<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhaCungCap extends Model
{
    protected $table = 'nha_cung_cap';
    protected $primaryKey = 'ncc_id';
    public $timestamps = false;
    
    protected $fillable = [
        'ten_ncc',
        'dia_chi',
        'ma_so_thue',
        'sdt',
        'email',
        'mo_ta',
    ];

    /**
     * Get all phiếu nhập for this nhà cung cấp
     */
    public function phieuNhap()
    {
        return $this->hasMany(PhieuNhap::class, 'ncc_id', 'ncc_id');
    }
}
