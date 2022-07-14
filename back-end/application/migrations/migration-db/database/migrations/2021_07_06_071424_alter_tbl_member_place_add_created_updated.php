<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberPlaceAddCreatedUpdated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_place', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_place', 'created')) {
                $table->dateTime('created')->nullable();
            } 
            if (!Schema::hasColumn('tbl_member_place', 'updated')) {
                $table->dateTime('updated')->nullable();
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
        Schema::table('tbl_member_place', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_place', 'created')) {
                $table->dropColumn('created');
            }
            if (Schema::hasColumn('tbl_member_place', 'updated')) {
                $table->dropColumn('updated');
            }
        });
    }
}
