<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKhoIdToThuocTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('thuoc', function (Blueprint $table) {
            $table->integer('kho_id')->nullable()->after('thuoc_id');
            $table->foreign('kho_id')
                ->references('kho_id')
                ->on('kho')
                ->onDelete('set null');
                    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('thuoc', function (Blueprint $table) {
            $table->dropForeign(['kho_id']); // Drop foreign key constraint
            $table->dropColumn('kho_id'); // Drop kho_id column
        });
    }
}
