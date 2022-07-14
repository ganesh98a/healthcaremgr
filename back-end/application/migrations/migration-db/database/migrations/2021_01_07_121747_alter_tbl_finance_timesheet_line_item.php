<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceTimesheetLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_timesheet_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_timesheet_line_item', 'units')) {
                $table->dropColumn('units');
            }
        });

        Schema::table('tbl_finance_timesheet_line_item', function (Blueprint $table) {
            $table->decimal('units', 10, 2)->default('0.00')->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        # nothing to do
    }
}
