<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinancePayratePaypointAsRenameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_finance_payrate_paypoint')) {
        Schema::table('tbl_finance_payrate_paypoint', function (Blueprint $table) {
            $table->renameColumn('hourly_rate', 'rate_type');
        });

        Schema::table('tbl_finance_payrate_paypoint', function (Blueprint $table) {
            $table->unsignedInteger('rate_type')->change();
        });
      }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if (Schema::hasTable('tbl_finance_payrate_paypoint') && Schema::hasColumn('tbl_finance_payrate_paypoint','rate_type') ) {
        Schema::table('tbl_finance_payrate_paypoint', function (Blueprint $table) {
           
            $table->renameColumn('rate_type','hourly_rate');
        });

        Schema::table('tbl_finance_payrate_paypoint', function (Blueprint $table) {
             $table->float('hourly_rate',10,2)->change();
        });
      }
    }
}
