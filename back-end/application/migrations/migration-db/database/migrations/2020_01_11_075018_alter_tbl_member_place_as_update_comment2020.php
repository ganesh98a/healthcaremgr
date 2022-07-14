<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberPlaceAsUpdateComment2020 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_place', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_member_place','type')) {
            $table->unsignedSmallInteger('type')->unsigned()->comment('1-Favourite, 2-Least Favourite, 3-Non of these')->change();
            } 
        });

        Schema::table('tbl_participant_place', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_participant_place','type')) {
            $table->unsignedSmallInteger('type')->unsigned()->comment('1-Favourite, 2-Least Favourite, 3-Non of these')->change();
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
             if (Schema::hasColumn('tbl_member_place','type')) {
                $table->unsignedSmallInteger('type')->unsigned()->comment('1-Active, 2-Inactive, 3-Non of these')->change();
            }
        });

        Schema::table('tbl_participant_place', function (Blueprint $table) {
             if (Schema::hasColumn('tbl_participant_place','type')) {
                $table->unsignedSmallInteger('type')->unsigned()->comment('1- Favourite, 2- Least Favourite')->change();
            }
        });
    }
}
