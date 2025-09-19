<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhachHang extends Model
{
    protected $table = 'khach_hang';
    protected $primaryKey = 'khach_hang_id';
    public $timestamps = false;
    
        protected $fillable = [
            'sdt',
            'ho_ten',
            'trang_thai',
        ];

    /**
     * Get all đơn bán lẻ for this khách hàng
     */
    public function donBanLe()
    {
        return $this->hasMany(DonBanLe::class, 'khach_hang_id', 'khach_hang_id');
    }
}
