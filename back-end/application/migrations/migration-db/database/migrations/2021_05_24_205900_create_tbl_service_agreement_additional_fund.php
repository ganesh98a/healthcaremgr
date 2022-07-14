<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblServiceAgreementAdditionalFund extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_service_agreement_additional_fund')) {
            Schema::create('tbl_service_agreement_additional_fund', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('service_agreement_id')->comment("tbl_service_agreement.id");
                $table->text('additional_title')->nullable()->comment('Title of the aditional funds');
                $table->double('additional_price', 10, 2);
                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->timestamp('created')->useCurrent();
                $table->unsignedInteger('created_by');               
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
        if (!Schema::hasTable('tbl_service_agreement_additional_fund')) {
            Schema::dropIfExists('tbl_service_agreement_additional_fund');
        }
    }
}
