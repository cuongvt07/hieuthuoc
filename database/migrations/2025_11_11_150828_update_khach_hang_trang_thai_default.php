<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Đổi default value của trang_thai từ 1 sang 0
        // 0 = active (hoạt động), 1 = suspended (đình chỉ)
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->tinyInteger('trang_thai')->default(0)->change();
        });
        
        Schema::table('nha_cung_cap', function (Blueprint $table) {
            $table->tinyInteger('trang_thai')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->tinyInteger('trang_thai')->default(1)->change();
        });
        
        Schema::table('nha_cung_cap', function (Blueprint $table) {
            $table->tinyInteger('trang_thai')->default(1)->change();
        });
    }
};
