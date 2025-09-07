<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable
{
    use Notifiable;
    
    protected $table = 'nguoi_dung';
    protected $primaryKey = 'nguoi_dung_id';
    public $timestamps = false;
    
    protected $fillable = [
        'ten_dang_nhap',
        'ho_ten',
        'email',
        'sdt',
        'vai_tro',
        'trang_thai',
        'mat_khau_hash',
    ];

    protected $hidden = [
        'mat_khau_hash',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->mat_khau_hash;
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin()
    {
        return $this->vai_tro === 'admin';
    }

    /**
     * Check if the user is a duoc_si
     */
    public function isDuocSi()
    {
        return $this->vai_tro === 'duoc_si';
    }

    /**
     * Get all đơn bán lẻ created by this user
     */
    public function donBanLe()
    {
        return $this->hasMany(DonBanLe::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }

    /**
     * Get all phiếu nhập created by this user
     */
    public function phieuNhap()
    {
        return $this->hasMany(PhieuNhap::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }
}
