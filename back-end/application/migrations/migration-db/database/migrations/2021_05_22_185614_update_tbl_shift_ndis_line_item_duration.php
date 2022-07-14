<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblShiftNdisLineItemDuration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            # update tbl_shift_ndis_line_item for duration
            DB::statement("UPDATE `tbl_shift_ndis_line_item` SET `duration` = (
                CASE 
                WHEN LENGTH(duration_old) = 1 THEN CONCAT('0', duration_old, ':00:00')
                WHEN LENGTH(duration_old) = 2 THEN CONCAT(duration_old, ':00:00')
                ELSE '00:00:00'
                END
            )  WHERE 1 ");
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
            # update tbl_shift_ndis_line_item for duration
            DB::statement("UPDATE `tbl_shift_ndis_line_item` SET `duration` = '00:00:00'  WHERE 1 ");
        });
    }
}
