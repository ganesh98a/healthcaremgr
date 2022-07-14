<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceTimesheetLineItem2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_timesheet_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_timesheet_line_item', 'external_reference')) {
                $table->string('external_reference')->nullable()->after("total_cost");
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
        Schema::table('tbl_finance_timesheet_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_timesheet_line_item', 'external_reference')) {
                $table->dropColumn('external_reference');
            }
        });
    }
}
