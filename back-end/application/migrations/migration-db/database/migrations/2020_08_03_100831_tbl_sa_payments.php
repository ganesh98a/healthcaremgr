<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblSaPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !  Schema::hasTable('tbl_sa_payments')) {
            Schema::create('tbl_sa_payments', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_agreement_id')->comment('tbl_service_agreement.id');
            $table->foreign('service_agreement_id')->references('id')->on('tbl_service_agreement');
            $table->unsignedSmallInteger('managed_type')->comment("1- Portal, 2- Plan, 3- Self")->nullable();
            $table->unsignedSmallInteger('service_booking_creator')->comment("1- Participant/Agent, 2- ONCALL")->nullable();
            $table->unsignedInteger('organisation_id')->nullable();
            $table->string('organisation_select', 255)->nullble();
            $table->string('organisation_contact_select', 255)->nullble();
            $table->unsignedInteger('organisation_contact_id')->nullable();
            $table->string('self_type_contact_name', 255)->nullble();
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('updated_date')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');            
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
        Schema::dropIfExists('tbl_sa_payments');
    }
}
