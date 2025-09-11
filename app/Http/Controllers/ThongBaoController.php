<?php

namespace App\Http\Controllers;

use App\Models\LoThuoc;
use App\Models\ThongBao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ThongBaoController extends Controller
{
    public function checkExpiredMedicines()
    {
        $today = Carbon::today();
        $threshold = $today->copy()->addMonths(3)->endOfDay();

        $rows = [];
        LoThuoc::with('thuoc')
            ->whereDate('han_su_dung', '>', $today)         // chưa hết hạn
            ->whereDate('han_su_dung', '<=', $threshold)    // < 3 tháng
            ->where('ton_kho_hien_tai', '>', 0)             // còn hàng
            ->chunkById(500, function ($lots) use (&$rows, $today) {
                foreach ($lots as $lot) {
                    $days = $today->diffInDays(Carbon::parse($lot->han_su_dung));
                    $tenThuoc = optional($lot->thuoc)->ten_thuoc ?? ('Thuốc ID ' . $lot->thuoc_id);

                    $rows[] = [
                        'loai'      => 'sap_het_han',
                        'noi_dung'  => "{$tenThuoc} (Lô #{$lot->ma_lo}) sẽ hết hạn trong {$days} ngày",
                        'da_doc'    => false,
                        'thoi_gian' => now(),
                        'thuoc_id'  => $lot->thuoc_id,
                        'lo_id'     => $lot->lo_id,
                        'created_at'=> now(),
                        'updated_at'=> now(),
                    ];
                }
            });

        // Chèn/chặn trùng theo (lo_id, loai)
        if (!empty($rows)) {
            ThongBao::upsert(
                $rows,
                ['lo_id', 'loai'],                 // unique keys
                ['noi_dung', 'thoi_gian', 'updated_at'] // các cột cập nhật nếu đã tồn tại
            );
        }

        return response()->json([
            'status' => 'ok',
            'content' => $rows,
            'created_or_updated' => count($rows),
        ]);
    }

    public function getUnreadNotifications()
    {
        return ThongBao::with(['thuoc', 'loThuoc'])
            ->where('da_doc', false)
            ->orderByDesc('thoi_gian')
            ->get();
    }

    public function markAsRead($id)
    {
        $notification = ThongBao::findOrFail($id);
        $notification->update(['da_doc' => true]);

        return response()->json(['success' => true]);
    }
}
