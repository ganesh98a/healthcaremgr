<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAmendmentAsUpdateColumnDatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_amendment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_amendment', 'start_time')) {
                $table->time('start_time')->default('00:00:00')->change();
            }
            if (Schema::hasColumn('tbl_shift_amendment', 'end_time')) {
                $table->time('end_time')->default('00:00:00')->change();;
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
        Schema::table('tbl_shift_amendment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_amendment', 'start_time')) {
                $table->int('start_time')->change();
            }
            if (Schema::hasColumn('tbl_shift_amendment', 'end_time')) {
                $table->int('end_time')->change();
            }
        });
    }
}
