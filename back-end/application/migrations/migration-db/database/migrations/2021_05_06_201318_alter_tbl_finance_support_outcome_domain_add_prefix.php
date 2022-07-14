<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceSupportOutcomeDomainAddPrefix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_support_outcome_domain', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_support_outcome_domain', 'prefix')) {
                $table->text('prefix')->nullable()->comment('Prefix')->after('name');
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
        Schema::table('tbl_finance_support_outcome_domain', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_support_outcome_domain', 'prefix')) {
                $table->dropColumn('prefix');
            }
        });
    }
}
