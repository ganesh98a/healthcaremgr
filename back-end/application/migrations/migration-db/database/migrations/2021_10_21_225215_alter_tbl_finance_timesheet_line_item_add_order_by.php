<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceTimesheetLineItemAddOrderBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_timesheet_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_timesheet_line_item', 'is_sys_generater')) {
                $table->unsignedSmallInteger('order')->default(0)->after('is_paid');               
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
            if (Schema::hasColumn('tbl_finance_timesheet_line_item', 'order')) {
                $table->dropColumn('order');
            }
        });
    }
}
