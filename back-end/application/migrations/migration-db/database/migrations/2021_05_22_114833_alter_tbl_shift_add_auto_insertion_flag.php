<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddAutoInsertionFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_ndis_line_item', 'auto_insert_flag')) {
                $table->unsignedTinyInteger('auto_insert_flag')->default(0)->comment('0 - No / 1 - Yes (Line item auto insertion if not in plan)')->after('duration');
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
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'auto_insert_flag')) {
                $table->dropColumn('auto_insert_flag');
            }
        });
    }
}
