<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuTonKho extends Model
{
    protected $table = 'lich_su_ton_kho';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'lo_id',
        'thuoc_id',
        'don_ban_le_id',
        'chi_tiet_don_id',
        'so_luong_thay_doi',
        'ton_kho_moi',
        'nguoi_dung_id',
        'loai_thay_doi',   // 'nhap', 'ban', 'dieu_chinh', 'chuyen_kho', 'hoan_tra'
        'mo_ta',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the lô thuốc for this lịch sử
     */
    public function loThuoc()
    {
        return $this->belongsTo(LoThuoc::class, 'lo_id', 'lo_id');
    }

    /**
     * Get the thuốc for this lịch sử
     */
    public function thuoc()
    {
        return $this->belongsTo(Thuoc::class, 'thuoc_id', 'thuoc_id');
    }

    // Removed kho() and phieuNhap() relationships as they are no longer used

    /**
     * Get the đơn bán lẻ for this lịch sử
     */
    public function donBanLe()
    {
        return $this->belongsTo(DonBanLe::class, 'don_ban_le_id', 'don_id');
    }

    /**
     * Get the chi tiết đơn bán lẻ for this lịch sử
     */
    public function chiTietDonBanLe()
    {
        return $this->belongsTo(ChiTietDonBanLe::class, 'chi_tiet_don_id', 'chi_tiet_id');
    }

    /**
     * Get the người dùng for this lịch sử
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id', 'nguoi_dung_id');
    }
}
