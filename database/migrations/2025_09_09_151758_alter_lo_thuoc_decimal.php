<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('lo_thuoc', function (Blueprint $table) {
            $table->decimal('tong_so_luong', 10, 2)->change();
            $table->decimal('ton_kho_hien_tai', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('lo_thuoc', function (Blueprint $table) {
            $table->integer('tong_so_luong')->change();
            $table->integer('ton_kho_hien_tai')->change();
        });
    }
};
