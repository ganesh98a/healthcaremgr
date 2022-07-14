<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnDocTypeSignedCrmParticipantStageDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant_stage_docs')) {
        Schema::table('tbl_crm_participant_stage_docs', function (Blueprint $table) {
            $table->unsignedInteger('document_type')->default(0)->comment("1=service agreement 2=funding concent 3=final service agreement");
            $table->unsignedInteger('document_signed')->default(0)->comment("0=No 2=Yes");
            $table->string('envelope_id',250)->default(0);
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
        if(Schema::hasTable('tbl_crm_participant_stage_docs') && Schema::hasColumn('tbl_crm_participant_stage_docs', 'document_type') && Schema::hasColumn('tbl_crm_participant_stage_docs', 'document_signed') && Schema::hasColumn('tbl_crm_participant_stage_docs', 'envelope_id') ) {
          Schema::table('tbl_crm_participant_stage_docs', function (Blueprint $table) {
              $table->dropColumn('document_type');
              $table->dropColumn('document_signed');
              $table->dropColumn('envelope_id');
          });
        }
    }
}
