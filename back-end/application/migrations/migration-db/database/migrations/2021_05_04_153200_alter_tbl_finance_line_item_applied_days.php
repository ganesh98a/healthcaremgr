<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceLineItemAppliedDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_line_item_applied_days', function (Blueprint $table) {
            $table->renameColumn('week_dayId', 'week_dayId_old')->change();
        });
        Schema::table('tbl_finance_line_item_applied_days', function (Blueprint $table) {
            $table->unsignedInteger('week_dayId')->nullable()->comment('priamry key tbl_finance_applied_days')->after('line_itemId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_line_item_applied_days', function (Blueprint $table) {
            $table->dropColumn('week_dayId');
            $table->renameColumn('week_dayId_old', 'week_dayId')->change();
        });
    }
}
