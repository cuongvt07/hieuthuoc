<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('thong_bao', function (Blueprint $table) {
            $table->id('thong_bao_id');
            $table->string('loai', 50); // 'sap_het_hang', 'sap_het_han'
            $table->text('noi_dung');
            $table->boolean('da_doc')->default(false);
            $table->dateTime('thoi_gian');

            // Create columns with matching data types (INT to match your existing tables)
            $table->integer('thuoc_id')->nullable();
            $table->integer('lo_id')->nullable();

            // Add foreign key constraints
            $table->foreign('thuoc_id')
                  ->references('thuoc_id')
                  ->on('thuoc')
                  ->cascadeOnDelete();

            $table->foreign('lo_id')
                  ->references('lo_id')
                  ->on('lo_thuoc')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('thong_bao');
    }
};