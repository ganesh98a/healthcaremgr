<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnCrmParticipantStageDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_stage_docs', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_crm_participant_stage_docs', 'signed_file_path')) {
                $table->string('signed_file_path',250);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_participant_stage_docs', function (Blueprint $table) {
            if(Schema::hasTable('tbl_crm_participant_stage_docs') && Schema::hasColumn('tbl_crm_participant_stage_docs', 'signed_file_path')) {
                $table->dropColumn('signed_file_path');
             }
        });
    }
}
