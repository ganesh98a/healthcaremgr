<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceLineItemUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            $table->datetime('start_date')->nullable()->change();
            $table->datetime('end_date')->nullable()->change();
            $table->unsignedSmallInteger('units')->nullable()->change();
            $table->unsignedInteger('support_registration_group')->comment('priamry key tbl_finance_support_registration_group')->nullable()->change();
            $table->unsignedInteger('support_outcome_domain')->comment('priamry key tbl_finance_support_outcome_domain')->nullable()->change();
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
            $table->datetime('start_date')->nullable(false)->change();
            $table->datetime('end_date')->nullable(false)->change();
            $table->unsignedSmallInteger('units')->nullable(false)->change();
            $table->unsignedInteger('support_registration_group')->comment('priamry key tbl_finance_support_registration_group')->nullable(false)->change();
            $table->unsignedInteger('support_outcome_domain')->comment('priamry key tbl_finance_support_outcome_domain')->nullable(false)->change();
        });
    }
}
