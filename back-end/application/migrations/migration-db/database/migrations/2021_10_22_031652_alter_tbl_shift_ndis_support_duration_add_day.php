<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftNdisSupportDurationAddDay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_ndis_support_duration', function (Blueprint $table) {
            $table->date('date')->nullable()->comment('duration of the date')->after('duration');
            $table->string('day', 50)->nullable()->comment('weekday / saturday / sunday / public_holiday')->after('date');
            $table->time('duration')->default('00:00:00')->comment('hh:mm:ss')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift_ndis_support_duration', function (Blueprint $table) {
            $table->time('duration')->default('00:00:00')->comment('hh:mm:ss')->nullable(false)->change();
            if (Schema::hasColumn('tbl_shift_ndis_support_duration', 'date')) {
                $table->dropColumn('date');
            }
            if (Schema::hasColumn('tbl_shift_ndis_support_duration', 'day')) {
                $table->dropColumn('day');
            }
        });
    }
}
