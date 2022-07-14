<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOrganisationAdditionalBillingInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_organisation_additional_billing_info', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('orgnisation_id')->nullable()->comment('tbl_organisation.id');
            $table->foreign('orgnisation_id')->references('id')->on('tbl_organisation')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('invoice_type');
            $table->unsignedInteger('invoice_batch');
            $table->unsignedInteger('cost_code');
            $table->unsignedInteger('site_discount');
            $table->smallInteger('confirm_billing_info');
            $table->unsignedSmallInteger('archive')->default(0)->comment('0 -Not/1- Yes');
            $table->unsignedInteger('created_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('created_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('updated_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_organisation_additional_billing_info', function (Blueprint $table) {
            Schema::dropIfExists('tbl_organisation_additional_billing_info');
        });
    }
}
