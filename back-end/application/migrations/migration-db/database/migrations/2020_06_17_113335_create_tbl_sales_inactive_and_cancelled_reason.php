<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSalesInactiveAndCancelledReason extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_sales_inactive_and_cancelled_reason', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id')->comment('1 - tbl_service_agreement.id | 2 - tbl_need_assessment.id | 3 - tbl_crm_risk_assessment.id');
            $table->unsignedInteger('entity_type')->comment('1 - service agreement, 2 - need assessment, 3 - crm_risk assessment');
            $table->unsignedInteger('reason_reference_data_type_id')->comment('tbl_reference_data_type.id');
            
            $table->unsignedInteger('reason_id')->comment('tbl_references.id with depend on tbl_reference_data_type.key_name');
            $table->text('reason_note')->nullable();
            $table->dateTime('created');
            $table->unsignedInteger('created_by')->comment("tbl_member.id");
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });
		
		$seeder = new ReferenceDataSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_sales_inactive_and_cancelled_reason');
    }

}
