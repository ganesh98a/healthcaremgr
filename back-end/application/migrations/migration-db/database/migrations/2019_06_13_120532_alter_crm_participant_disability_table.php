<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantDisabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_crm_participant_disability')) {
        Schema::table('tbl_crm_participant_disability', function (Blueprint $table) {
            $table->string('primary_fomal_diagnosis_desc',100)->change();
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
      if (Schema::hasTable('tbl_crm_participant_disability') && Schema::hasColumn('tbl_crm_participant_disability','primary_fomal_diagnosis_desc')) {
        Schema::table('tbl_crm_participant_disability', function (Blueprint $table) {
            $table->dropColumn('primary_fomal_diagnosis_desc');
        });
      }
    }
}
