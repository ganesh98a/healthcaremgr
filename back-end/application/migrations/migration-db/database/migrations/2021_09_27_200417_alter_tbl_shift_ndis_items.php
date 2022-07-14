<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftNdisItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_ndis_line_item','line_item_id')) {
                $table->renameColumn('line_item_id', 'line_item_price_id');
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
            if (Schema::hasColumn('tbl_shift_ndis_line_item','line_item_price_id')) {
                $table->renameColumn('line_item_price_id', 'line_item_id');
            }
        });
    }
}
