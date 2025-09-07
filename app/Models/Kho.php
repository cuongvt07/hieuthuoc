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
}
