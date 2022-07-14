<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftNdisLineItemAddAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_ndis_line_item', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('duration');
            }
            if (!Schema::hasColumn('tbl_shift_ndis_line_item', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->comment('duration * price')->after('price');
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item','duration')) {
                $table->renameColumn('duration', 'duration_old');
            }
        });
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_ndis_line_item', 'duration')) {
                $table->time('duration')->default('00:00:00')->after('sa_line_item_id')->comment('hh:mm:ss');
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
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'duration')) {
                $table->dropColumn('duration');
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'duration_old')) {
                $table->renameColumn('duration_old', 'duration');
            }
        });
    }
}
