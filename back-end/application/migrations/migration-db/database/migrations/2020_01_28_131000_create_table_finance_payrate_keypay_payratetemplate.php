<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFinancePayrateKeypayPayratetemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tbl_finance_payrate_keypay_payratetemplate')){
            Schema::create('tbl_finance_payrate_keypay_payratetemplate', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('payrate_id')->comment('auto increment id tbl_finance_payrate');
                $table->string('keypay_payratetemplate_id',255)->nullable()->comment('keypay api return unique id');
                $table->string('external_id',255)->nullable()->comment('keypay unique externalId mapping');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->unsignedSmallInteger('archive')->default('0');
            });
        }

        if(!Schema::hasTable('tbl_finance_payrate_keypay_payratecategory')){
            Schema::create('tbl_finance_payrate_keypay_payratecategory', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('payrate_id')->comment('auto increment id tbl_finance_payrate');
                $table->unsignedInteger('rate_type')->comment('auto increment id tbl_finance_addtitonal_paypoint_ratetype');
                $table->string('keypay_pay_category_id',255)->nullable()->comment('keypay api return unique id');
                $table->string('external_id',255)->nullable()->comment('keypay unique externalId mapping');
                $table->unsignedSmallInteger('is_primary_category')->default('0');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->unsignedSmallInteger('archive')->default('0');
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
        Schema::dropIfExists('tbl_finance_payrate_keypay_payratetemplate');
        Schema::dropIfExists('tbl_finance_payrate_keypay_payratecategory');
    }
}
