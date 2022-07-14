<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCrmParticipantDisabilityColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_disability', function (Blueprint $table) {
             $table->renameColumn('fomal_diagnosis', 'primary_fomal_diagnosis_desc');
             $table->renameColumn('fomal_diagnosis_desc', 'secondary_fomal_diagnosis_desc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if (Schema::hasTable('tbl_crm_participant_disability') && Schema::hasColumn('tbl_crm_participant_disability','primary_fomal_diagnosis_desc') && Schema::hasColumn('tbl_crm_participant_disability','secondary_fomal_diagnosis_desc')) {
        Schema::table('tbl_crm_participant_disability', function (Blueprint $table) {
            $table->renameColumn('primary_fomal_diagnosis_desc','fomal_diagnosis');
            $table->renameColumn('secondary_fomal_diagnosis_desc','fomal_diagnosis_desc');
        });
      }
    }
}
