<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kho extends Model
{
    protected $table = 'kho';
    protected $primaryKey = 'kho_id';
    public $timestamps = false;
    
    protected $fillable = [
        'ten_kho',
        'dia_chi',
        'ghi_chu',
    ];

    /**
     * Get all lô thuốc in this kho
     */
    public function loThuoc()
    {
        return $this->hasMany(LoThuoc::class, 'kho_id', 'kho_id');
    }

    /**
     * Get all thuốc in this kho through lô thuốc
     */
    public function thuoc()
    {
        return $this->belongsToMany(Thuoc::class, 'lo_thuoc', 'kho_id', 'thuoc_id', 'kho_id', 'thuoc_id')
                    ->withPivot(['ton_kho_hien_tai', 'ngay_san_xuat', 'han_su_dung'])
                    ->distinct();
    }
}
