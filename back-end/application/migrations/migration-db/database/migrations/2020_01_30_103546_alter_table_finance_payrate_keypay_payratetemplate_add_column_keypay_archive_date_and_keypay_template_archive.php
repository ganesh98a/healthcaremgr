<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFinancePayrateKeypayPayratetemplateAddColumnKeypayArchiveDateAndKeypayTemplateArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_finance_payrate_keypay_payratetemplate')){
            Schema::table('tbl_finance_payrate_keypay_payratetemplate', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_payrate_keypay_payratetemplate','keypay_archive_date')){
                    $table->dateTime('keypay_archive_date')->nullable()->default('0000-00-00 00:00:00')->comment('when pay rate tamplete delete on keypay api');
                }
                if(!Schema::hasColumn('tbl_finance_payrate_keypay_payratetemplate','keypay_template_archive')){
                    $table->unsignedSmallInteger('keypay_template_archive')->nullable()->default(0)->comment('1- deleted ,2-error on deleted,0-activated on HCM app');
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
        if(Schema::hasTable('tbl_finance_payrate_keypay_payratetemplate')){
            Schema::table('tbl_finance_payrate_keypay_payratetemplate', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_payrate_keypay_payratetemplate','keypay_archive_date')){
                    $table->dropColumn('keypay_archive_date');
                }
                if(Schema::hasColumn('tbl_finance_payrate_keypay_payratetemplate','keypay_template_archive')){
                    $table->dropColumn('keypay_template_archive');
                }
                //
            });
        }
    }
}
