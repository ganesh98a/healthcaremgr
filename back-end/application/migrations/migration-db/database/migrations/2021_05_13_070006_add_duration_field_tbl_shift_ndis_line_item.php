<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDurationFieldTblShiftNdisLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_shift_ndis_line_item')) {
            Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_shift_ndis_line_item', 'duration')) {
                    $table->unsignedInteger('duration')->nullable()->after("sa_line_item_id")
                    ->comments('Shift duration spend based on day');
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
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            //
        });
    }
}
