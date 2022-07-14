<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblShiftNdisLineCategory extends Migration
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
            DB::statement("UPDATE `tbl_shift_ndis_line_item` SET `category` = '1'  WHERE 1 ");
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
            DB::statement("UPDATE `tbl_shift_ndis_line_item` SET `category` = NULL  WHERE 1 ");
        });
    }
}
