<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->tinyInteger('trang_thai')->default(1)->after('sdt');
        });
        Schema::table('nha_cung_cap', function (Blueprint $table) {
            $table->tinyInteger('trang_thai')->default(1)->after('ma_so_thue');
        });
    }

    public function down()
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->dropColumn('trang_thai');
        });
        Schema::table('nha_cung_cap', function (Blueprint $table) {
            $table->dropColumn('trang_thai');
        });
    }
};
