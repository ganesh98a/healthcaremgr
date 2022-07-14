<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftMemberAsAddColumnIsRestricted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
          $table->boolean('is_restricted')->comment('is_whether_the_member_restricted_for_this_shift')->after('member_id');
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
            if (Schema::hasTable('tbl_shift_member') && Schema::hasColumn('tbl_shift_member','is_restricted')) {
            Schema::table('tbl_shift_member', function (Blueprint $table) {
                $table->dropColumn('is_restricted');
                
            });
        }
    });
    }
}
