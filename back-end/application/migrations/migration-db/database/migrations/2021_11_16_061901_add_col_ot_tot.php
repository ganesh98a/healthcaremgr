<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColOtTot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_timesheet', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_timesheet', 'ot_15_total')) {
                $table->decimal('ot_15_total',10,2)->default(0)->after('updated_by')->comment('Total units of Overtime 1.5');
            }
            if (!Schema::hasColumn('tbl_finance_timesheet', 'ot_20_total')) {
                $table->decimal('ot_20_total',10,2)->default(0)->after('ot_15_total')->comment('Total units of Overtime 2.0');
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
        Schema::table('tbl_finance_timesheet', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_timesheet', 'ot_15_total')) {
                $table->dropColumn('ot_15_total');
            }
            if (Schema::hasColumn('tbl_finance_timesheet', 'ot_20_total')) {
                $table->dropColumn('ot_20_total');
            }
        });
    }
}
