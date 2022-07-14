<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceSupportCategoryAddPrefix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_support_category', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_support_category', 'prefix')) {
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
        Schema::table('tbl_finance_support_category', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_support_category', 'prefix')) {
                $table->dropColumn('prefix');
            }
        });
    }
}
