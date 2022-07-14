<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceTimesheetLineItemIsPaid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_timesheet_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_timesheet_line_item', 'is_paid')) {
                $table->unsignedInteger('is_paid')->default('0')->comment('0 = not paid, 1 = paid')->after("archive");
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
            if (Schema::hasColumn('tbl_finance_timesheet_line_item', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
}
