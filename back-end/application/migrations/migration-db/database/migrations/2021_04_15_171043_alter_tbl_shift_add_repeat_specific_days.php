<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddRepeatSpecificDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'repeat_specific_days')) {
                $table->unsignedSmallInteger('repeat_specific_days')->after('repeat_option')->nullable()->default(0)->comment('1 - Yes / 0 - No');
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
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'repeat_specific_days')) {
                $table->dropColumn('repeat_specific_days');
            }
        });
    }
}
