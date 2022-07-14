<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblLogViewed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_viewed_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_type')->comment('1 - Application / 2 - Applicant / 3 - Leads / 4 - Opportunity / 5 - Service Agreement');
            $table->unsignedInteger('entity_id')->comment('reference of (entity_type = 1 - tbl_recruitment_applicant_applied_application / 2 - tbl_recruitment_applicant / 3 - tbl_leads / 4 - tbl_opportunity / 5 - tbl_service_agreement).id');
            $table->dateTime('viewed_date')->nullable();
            $table->unsignedInteger('viewed_by')->nullable();
            $table->mediumText('history')->nullable();
            $table->foreign('viewed_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_viewed_log');
    }
}
