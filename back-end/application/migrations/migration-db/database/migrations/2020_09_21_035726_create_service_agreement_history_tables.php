<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceAgreementHistoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_service_agreement_history')) {
            Schema::create('tbl_service_agreement_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('service_agreement_id')->unsigned()->comment('the associated service agreement');
                $table->foreign('service_agreement_id')->references('id')->on('tbl_service_agreement')->onDelete('cascade');
                $table->unsignedInteger('created_by')->comment('the user who initiated the field change, or zero if initiated by the system');
                $table->foreign('created_by')->references('id')->on('tbl_member');          // do not cascade
                $table->dateTimeTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));    // not nullable
            });
        }

        if (!Schema::hasTable('tbl_service_agreement_field_history')) {
            Schema::create('tbl_service_agreement_field_history', function (Blueprint $table) {
                $fields = [
                    'owner', 'status', 'grand_total', 'sub_total', 'tax', 'additional_services', 'additional_services_custom', 'customer_signed_date', 'contract_start_date', 'contract_end_date', 'plan_start_date', 'plan_end_date', 'signed_by', 'created_by', 'goals'
                ];

                $table->bigIncrements('id');
                $table->bigInteger('history_id')->unsigned()->comment('the assosciated service agreement history item');
                $table->foreign('history_id')->references('id')->on('tbl_service_agreement_history')->onDelete('cascade');
                $table->integer('service_agreement_id')->unsigned()->comment('the associated service agreement');
                $table->foreign('service_agreement_id')->references('id')->on('tbl_service_agreement')->onDelete('cascade');
                $table->enum('field', $fields);
                $table->mediumText('value')->comment('current field value');
                $table->mediumText('prev_val')->comment('previous field value')->nullable();
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
        Schema::dropIfExists('tbl_service_agreement_field_history');
        Schema::dropIfExists('tbl_service_agreement_history');        
    }
}
