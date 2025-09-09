<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lich_su_ton_kho', function (Blueprint $table) {
            // Xoá 2 cột
            $table->dropColumn(['kho_id', 'phieu_nhap_id']);
        });

        // Cập nhật ENUM (không làm được bằng Schema builder, nên phải dùng raw SQL)
        DB::statement("
            ALTER TABLE `lich_su_ton_kho` 
            MODIFY `loai_thay_doi` 
            ENUM('nhap','ban','dieu_chinh','chuyen_kho','hoan_tra') NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('lich_su_ton_kho', function (Blueprint $table) {
            // Thêm lại cột
            $table->unsignedBigInteger('kho_id')->after('thuoc_id');
            $table->unsignedBigInteger('phieu_nhap_id')->nullable()->after('kho_id');
        });

        // Khôi phục ENUM cũ
        DB::statement("
            ALTER TABLE `lich_su_ton_kho` 
            MODIFY `loai_thay_doi` 
            ENUM('nhap','ban','dieu_chinh','chuyen_kho') NOT NULL
        ");
    }
};
