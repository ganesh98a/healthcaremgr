<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddActualSbScheduleSbColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'actual_sb_status')) {
                $table->unsignedInteger('actual_sb_status')->comment('1=sb exist,2= not exist,3=exist_without_sign')->after('actual_sa_id');
            }
            if (!Schema::hasColumn('tbl_shift', 'scheduled_sb_status')) {
                $table->unsignedInteger('scheduled_sb_status')->comment('1=sb exist,2= not exist,3=exist_without_sign')->after('scheduled_sa_id');
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
            if (Schema::hasColumn('tbl_shift', 'actual_sb_status')) {
                 $table->dropColumn('actual_sb_status');
            }
        });
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'scheduled_sb_status')) {
                 $table->dropColumn('scheduled_sb_status');
            }
        });
    }
}
