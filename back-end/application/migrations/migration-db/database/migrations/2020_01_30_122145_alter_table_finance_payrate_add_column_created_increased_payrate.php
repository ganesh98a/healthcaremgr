<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFinancePayrateAddColumnCreatedIncreasedPayrate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_finance_payrate')){
            Schema::table('tbl_finance_payrate', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_payrate','created_increased_payrate')){
                    $table->unsignedSmallInteger('created_increased_payrate')->nullable()->default(0)->comment('0- not created yet, 1 for already created')->after('comments');
                }
                if(!Schema::hasColumn('tbl_finance_payrate','increased_payrate_id_parent')){
                    $table->unsignedInteger('increased_payrate_id_parent')->default(0)->nullable()->comment('auto increment id tbl_finance_payrate for ref copy')->after('created_increased_payrate');
                }
                //
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
        if(Schema::hasTable('tbl_finance_payrate')){
            Schema::table('tbl_finance_payrate', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_payrate','created_increased_payrate')){
                    $table->dropColumn('created_increased_payrate');
                }
                if(Schema::hasColumn('tbl_finance_payrate','increased_payrate_id_parent')){
                    $table->dropColumn('increased_payrate_id_parent');
                }
                //
            });
        }
    }
}
