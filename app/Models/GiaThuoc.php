<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiaThuoc extends Model
{
    protected $table = 'gia_thuoc';
    protected $primaryKey = 'gia_id';
    public $timestamps = false;
    
    protected $fillable = [
        'thuoc_id',
        'gia_ban',
        'ngay_bat_dau',
        'ngay_ket_thuc',
    ];

    /**
     * Get the thuốc that this price belongs to
     */
    public function thuoc()
    {
        return $this->belongsTo(Thuoc::class, 'thuoc_id', 'thuoc_id');
    }
}
