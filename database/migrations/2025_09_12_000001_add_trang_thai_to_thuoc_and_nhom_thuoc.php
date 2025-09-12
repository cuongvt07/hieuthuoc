<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('thuoc', function (Blueprint $table) {
            $table->tinyInteger('trang_thai')->default(0)->after('ti_le_quy_doi');
        });
        Schema::table('nhom_thuoc', function (Blueprint $table) {
            $table->tinyInteger('trang_thai')->default(0)->after('mo_ta');
        });
    }

    public function down()
    {
        Schema::table('thuoc', function (Blueprint $table) {
            $table->dropColumn('trang_thai');
        });
        Schema::table('nhom_thuoc', function (Blueprint $table) {
            $table->dropColumn('trang_thai');
        });
    }
};
