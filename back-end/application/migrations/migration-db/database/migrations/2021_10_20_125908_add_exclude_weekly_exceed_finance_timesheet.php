<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExcludeWeeklyExceedFinanceTimesheet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_timesheet', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_timesheet', 'is_exclude_ot')){
                # Set flag true for timesheet has interrupted S/O break or OT created by weekly maximum hours exceed
                $table->unsignedInteger('is_exclude_ot')->default(0)->comment('Set flag true if timesheet has interrupted S/O break or weekly maximum hours exceed OT');
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
        Schema::dropIfExists('tbl_finance_timesheet');
    }
}
