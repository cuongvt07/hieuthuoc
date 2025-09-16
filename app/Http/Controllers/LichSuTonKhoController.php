<?php

namespace App\Http\Controllers;

use App\Models\LichSuTonKho;
use App\Models\LoThuoc;
use App\Models\Thuoc;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LichSuTonKhoController extends Controller
{
    public function index(Request $request)
    {
        $query = LichSuTonKho::with(['loThuoc.thuoc', 'nguoiDung']);

        // Lọc theo thuốc
        if ($request->filled('thuoc_id')) {
            $query->whereHas('loThuoc', function ($q) use ($request) {
                $q->where('thuoc_id', $request->thuoc_id);
            });
        }

        // Lọc theo loại thay đổi
        if ($request->filled('loai_thay_doi')) {
            $query->where('loai_thay_doi', $request->loai_thay_doi);
        }

        // Lọc theo thời gian
        if ($request->filled('tu_ngay')) {
            $tuNgay = Carbon::createFromFormat('d/m/Y', $request->tu_ngay)->startOfDay();
            $query->where('created_at', '>=', $tuNgay);
        }
        if ($request->filled('den_ngay')) {
            $denNgay = Carbon::createFromFormat('d/m/Y', $request->den_ngay)->endOfDay();
            $query->where('created_at', '<=', $denNgay);
        }

        // Sắp xếp
        $query->orderBy('created_at', 'desc');

        $lichSu = $query->paginate(20);
        $thuocs = Thuoc::where('trang_thai', 1)->orderBy('ten_thuoc')->get();

        return view('lich-su-ton-kho.index', compact('lichSu', 'thuocs'));
    }
}
