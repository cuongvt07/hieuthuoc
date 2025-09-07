<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thuoc extends Model
{
    protected $table = 'thuoc';
    protected $primaryKey = 'thuoc_id';
    public $timestamps = false;
    
    protected $fillable = [
        'nhom_id',
        'ma_thuoc',
        'ten_thuoc',
        'mo_ta',
        'don_vi_goc',
        'don_vi_ban',
        'ti_le_quy_doi',
    ];

    /**
     * Get the nhóm thuốc this thuốc belongs to
     */
    public function nhomThuoc()
    {
        return $this->belongsTo(NhomThuoc::class, 'nhom_id', 'nhom_id');
    }

    /**
     * Get the giá thuốc for this thuốc
     */
    public function giaThuoc()
    {
        return $this->hasMany(GiaThuoc::class, 'thuoc_id', 'thuoc_id');
    }

    /**
     * Get the lô thuốc for this thuốc
     */
    public function loThuoc()
    {
        return $this->hasMany(LoThuoc::class, 'thuoc_id', 'thuoc_id');
    }

    /**
     * Get the current price of the thuốc
     */
    public function giaBanHienTai()
    {
        $today = date('Y-m-d');
        return $this->giaThuoc()
            ->where('ngay_bat_dau', '<=', $today)
            ->where(function($query) use ($today) {
                $query->where('ngay_ket_thuc', '>=', $today)
                      ->orWhereNull('ngay_ket_thuc');
            })
            ->orderBy('ngay_bat_dau', 'desc')
            ->first();
    }
}
