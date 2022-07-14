<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblShiftNdisLineItemAddIsOldPriceColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_ndis_line_item', 'is_old_price')) {
                $table->unsignedSmallInteger('is_old_price')->default(0)->comment("0- false, 1- true")->after('auto_insert_flag');
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
            $table->dropColumn(['is_old_price']);
        });
    }
}
