<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFinancePayrateKeypayPayratecategoryAddColumnKeypayArchiveDateAndKeypayArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_finance_payrate_keypay_payratecategory')){
            Schema::table('tbl_finance_payrate_keypay_payratecategory', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_payrate_keypay_payratecategory','keypay_archive_date')){
                    $table->dateTime('keypay_archive_date')->nullable()->default('0000-00-00 00:00:00')->comment('when pay rate tamplete delete on keypay api');
                }
                if(!Schema::hasColumn('tbl_finance_payrate_keypay_payratecategory','keypay_category_archive')){
                    $table->unsignedSmallInteger('keypay_category_archive')->nullable()->default(0)->comment('1- deleted ,2-error on deleted,0-activated on HCM app');
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
        if(Schema::hasTable('tbl_finance_payrate_keypay_payratecategory')){
            Schema::table('tbl_finance_payrate_keypay_payratecategory', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_payrate_keypay_payratecategory','keypay_archive_date')){
                    $table->dropColumn('keypay_archive_date');
                }
                if(Schema::hasColumn('tbl_finance_payrate_keypay_payratecategory','keypay_category_archive')){
                    $table->dropColumn('keypay_category_archive');
                }
                //
            });
        }
    }
}
