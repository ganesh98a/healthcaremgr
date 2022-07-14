<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantDisabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_disability')) {
            Schema::create('tbl_crm_participant_disability', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->nullable(); 
                $table->unsignedTinyInteger('fomal_diagnosis')->nullable()->comment('1- Primary, 2- Secondary'); 
                $table->string('fomal_diagnosis_desc',100);
                $table->string('other_relevant_information',64)->nullable();
                $table->string('legal_issues',64)->nullable();
                $table->unsignedInteger('linked_fms_case_id')->nullable(); 
                $table->string('status',64)->nullable();
                $table->string('docs',255)->nullable();
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
        Schema::dropIfExists('tbl_crm_participant_disability');
    }
}
