<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinancePayrateAsAddColumnKeyPayArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_payrate', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_finance_payrate','key_pay_archive')){
                $table->unsignedInteger('key_pay_archive')->comment('marked as archive on keypay, 2 = edit someone and archive,1 = direct archive')->after('archive');
                $table->unsignedInteger('parent_pay_rate')->comment('payrate id')->after('key_pay_archive');
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
        Schema::table('tbl_finance_payrate', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_finance_payrate','key_pay_archive')){
                $table->dropColumn('key_pay_archive');
            }

            if(Schema::hasColumn('tbl_finance_payrate','parent_pay_rate')){
                $table->dropColumn('parent_pay_rate');
            }
        });
    }
}
