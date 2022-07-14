<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDatatypeNotesCrmParticipantStageNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_stage_notes', function (Blueprint $table) {
          if(Schema::hasColumn('tbl_crm_participant_stage_notes','notes'))
            $table->text('notes')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if (Schema::hasTable('tbl_crm_participant_stage_notes' && !Schema::hasColumn('tbl_crm_participant_stage_notes','notes'))) {
        Schema::table('tbl_crm_participant_stage_notes', function (Blueprint $table) {
            $table->string('notes',64)->nullable();
        });
      }
    }
}
