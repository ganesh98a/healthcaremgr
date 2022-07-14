<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceSupportRegistrationGroupPrefix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_support_registration_group', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_support_registration_group', 'prefix')) {
                $table->renameColumn('batchId', 'prefix');
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
        Schema::table('tbl_finance_support_registration_group', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_support_registration_group', 'prefix')) {
                $table->renameColumn('prefix', 'batchId');
            }
        });
    }
}
