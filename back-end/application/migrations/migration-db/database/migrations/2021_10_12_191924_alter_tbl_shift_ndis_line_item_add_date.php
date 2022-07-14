<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftNdisLineItemAddDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('duration');
            $table->date('end_date')->nullable()->after('start_date');
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
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
}
