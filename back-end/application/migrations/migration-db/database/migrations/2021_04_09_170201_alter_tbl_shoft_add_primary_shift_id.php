<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShoftAddPrimaryShiftId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'primary_shift_id')) {
                $table->unsignedSmallInteger('primary_shift_id')->after('shift_no')->nullable()->comment('reference of tbl_shift.id');
            }
            if (!Schema::hasColumn('tbl_shift', 'repeat_option')) {
                $table->unsignedSmallInteger('repeat_option')->after('primary_shift_id')->nullable()->comment('1 - Repeat fot tomorrow / 2 - Repeat for rest of the week / 3 - Repeat for specific / 4 - Repeat weekly / 5 - Repeat fortnightly / 6 - Repeat monthly');
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
            if (Schema::hasColumn('tbl_shift', 'primary_shift_id')) {
                $table->dropColumn('primary_shift_id');
            }
            if (Schema::hasColumn('tbl_shift', 'repeat_option')) {
                $table->dropColumn('repeat_option');
            }
        });
    }
}
