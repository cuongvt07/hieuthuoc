<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    protected $table = 'thong_bao';
    protected $primaryKey = 'thong_bao_id';
    
    protected $fillable = [
        'loai',
        'noi_dung',
        'da_doc',
        'thoi_gian',
        'thuoc_id',
        'lo_id'
    ];

    protected $casts = [
        'da_doc' => 'boolean',
        'thoi_gian' => 'datetime'
    ];

    public function thuoc()
    {
        return $this->belongsTo(Thuoc::class, 'thuoc_id', 'thuoc_id');
    }

    public function loThuoc()
    {
        return $this->belongsTo(LoThuoc::class, 'lo_id', 'lo_id');
    }
}
