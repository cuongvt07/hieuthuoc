<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('lich_su_ton_kho', function (Blueprint $table) {
            // DECIMAL(10,2) -> tối đa 99999999.99
            $table->decimal('so_luong_thay_doi', 10, 2)->change();
            $table->decimal('ton_kho_moi', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('lich_su_ton_kho', function (Blueprint $table) {
            $table->integer('so_luong_thay_doi')->change();
            $table->integer('ton_kho_moi')->change();
        });
    }
};
