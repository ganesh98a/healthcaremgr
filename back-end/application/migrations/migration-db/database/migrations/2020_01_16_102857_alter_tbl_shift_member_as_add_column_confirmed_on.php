<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftMemberAsAddColumnConfirmedOn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
          $table->datetime('confirm_on')->comment('date time when shift is confirm by member')->after('assign_type');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (Schema::hasTable('tbl_shift_member') && Schema::hasColumn('tbl_shift_member','confirm_on')) {
            Schema::table('tbl_shift_member', function (Blueprint $table) {
                $table->dropColumn('confirm_on');
                
            });
        }
    });
    }
}
