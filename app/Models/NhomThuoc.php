<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhomThuoc extends Model
{
    protected $table = 'nhom_thuoc';
    protected $primaryKey = 'nhom_id';
    public $timestamps = false;
    
    protected $fillable = [
        'ma_nhom',
        'ten_nhom',
        'mo_ta',
        'trang_thai',
    ];

    /**
     * Get all thuốc in this nhóm
     */
    public function thuoc()
    {
        return $this->hasMany(Thuoc::class, 'nhom_id', 'nhom_id');
    }
}
