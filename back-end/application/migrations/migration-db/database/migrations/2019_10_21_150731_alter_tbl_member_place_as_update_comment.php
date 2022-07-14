<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberPlaceAsUpdateComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_member_place')) {
            Schema::table('tbl_member_place', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_member_place','type')) {
                    $table->unsignedSmallInteger('type')->unsigned()->comment('1-Active, 2-Inactive, 3-Non of these')->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_member_place')) {
            Schema::table('tbl_member_place', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_member_place','type')) {
                    $table->unsignedSmallInteger('type')->unsigned()->comment('1- Favourite, 2- Least Favourite')->change();
                    
                }
            });
        }
    }
}
