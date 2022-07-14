<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDropTestColumnsCrmParticipantDisabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         /* Schema::table('tbl_crm_participant_disability', function (Blueprint $table) {
           $table->string('primary_fomal_diagnosis_desc', 255)->nullable()->change();
           $table->string('secondary_fomal_diagnosis_desc', 255)->nullable()->change();
           $table->string('other_relevant_information', 255)->nullable()->change();
         }); */
     }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       /* if (Schema::hasTable('tbl_crm_participant_disability') && Schema::hasColumn('tbl_crm_participant_disability','primary_fomal_diagnosis_desc','other_relevant_information')) {
         Schema::table('tbl_crm_participant_disability', function (Blueprint $table) {
               $table->string('primary_fomal_diagnosis_desc',100);
               $table->string('secondary_fomal_diagnosis_desc',100);
               $table->string('other_relevant_information',100);
         });
       } */
     }
}
