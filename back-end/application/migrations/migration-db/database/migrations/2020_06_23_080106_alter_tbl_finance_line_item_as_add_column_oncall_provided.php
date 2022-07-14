<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceLineItemAsAddColumnOncallProvided extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
           if (!Schema::hasColumn('tbl_finance_line_item', 'oncall_provided')) {
            $table->unsignedInteger('oncall_provided')->comment('0 -Not/1- Yes')->after('measure_by');
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
     Schema::table('tbl_finance_line_item', function (Blueprint $table) {
        if (Schema::hasColumn('tbl_finance_line_item', 'oncall_provided')) {
            $table->dropColumn('oncall_provided');
        }
    });
 }
}
