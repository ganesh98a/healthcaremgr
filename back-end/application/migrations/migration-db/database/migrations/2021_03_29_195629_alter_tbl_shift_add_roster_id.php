<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddRosterId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'roster_id')){
                $table->unsignedInteger('roster_id')->nullable()->comment('reference of tb_roster.id')->after('account_id');
                $table->foreign('roster_id')->references('id')->on('tbl_roster')->onUpdate('cascade')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'roster_id')) {
                $table->dropForeign(['roster_id']);
                $table->dropColumn('roster_id');
            }
        });
    }
}
