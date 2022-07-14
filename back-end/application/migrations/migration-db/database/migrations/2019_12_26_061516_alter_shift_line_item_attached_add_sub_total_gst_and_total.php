<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftLineItemAttachedAddSubTotalGstAndTotal extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift_line_item_attached', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_line_item_attached', 'sub_total')) {
                $table->double('sub_total', 10, 2)->comment('total of item cost (cost * qty) exclude gst')->after("cost");
            }
            if (!Schema::hasColumn('tbl_shift_line_item_attached', 'gst')) {
                $table->double('gst', 10, 2)->comment('gst of sub_total')->after("sub_total");
            }
            if (!Schema::hasColumn('tbl_shift_line_item_attached', 'total')) {
                $table->double('total', 10, 2)->comment('sum of sub_total and gst(sub_total + gst)')->after("gst");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_shift_line_item_attached', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_line_item_attached', 'sub_total')) {
                $table->dropColumn('sub_total');
            }
            if (Schema::hasColumn('tbl_shift_line_item_attached', 'gst')) {
                $table->dropColumn('gst');
            }
            if (Schema::hasColumn('tbl_shift_line_item_attached', 'total')) {
                $table->dropColumn('total');
            }
        });
    }

}
