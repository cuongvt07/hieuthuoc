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
        'kho_id',
        'ma_thuoc',
        'ten_thuoc',
        'mo_ta',
        'don_vi_goc',
        'don_vi_ban',
        'ti_le_quy_doi',
        'trang_thai',
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
     * Get the kho that this thuốc belongs to
     */
    public function kho()
    {
        return $this->belongsTo(Kho::class, 'kho_id', 'kho_id');
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
    
    /**
     * Tính tổng tồn kho của thuốc này trong tất cả các lô
     */
    public function tongTonKho()
    {
        return $this->loThuoc()->sum('ton_kho_hien_tai');
    }
    
    /**
     * Lấy tất cả các lô của thuốc còn hạn sử dụng và còn hàng
     * Ưu tiên lô nhập cũ nhất (FIFO - First In First Out)
     */
    public function getLoThuocConHang()
    {
        $today = date('Y-m-d');
        return $this->loThuoc()
            ->where('ton_kho_hien_tai', '>', 0)
            ->where('han_su_dung', '>=', $today)
            ->orderBy('ngay_san_xuat', 'asc') // Ưu tiên lô sản xuất trước (cũ nhất)
            ->orderBy('han_su_dung', 'asc')    // Nếu cùng ngày sản xuất, ưu tiên lô sắp hết hạn
            ->get();
    }
    
    /**
     * Lấy lô thuốc cũ nhất còn hàng và còn hạn sử dụng
     * Áp dụng nguyên tắc FIFO (First In First Out)
     */
    public function getLoThuocCuNhat()
    {
        $today = date('Y-m-d');
        return $this->loThuoc()
            ->where('ton_kho_hien_tai', '>', 0)
            ->where('han_su_dung', '>=', $today)
            ->orderBy('ngay_san_xuat', 'asc') // Ưu tiên lô sản xuất trước (cũ nhất)
            ->orderBy('han_su_dung', 'asc')    // Nếu cùng ngày sản xuất, ưu tiên lô sắp hết hạn
            ->first();
    }
}
