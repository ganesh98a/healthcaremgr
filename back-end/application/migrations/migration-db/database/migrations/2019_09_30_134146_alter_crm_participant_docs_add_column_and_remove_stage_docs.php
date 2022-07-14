<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantDocsAddColumnAndRemoveStageDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_crm_participant_stage_docs')) {
            Schema::dropIfExists('tbl_crm_participant_stage_docs');
        }
        if (Schema::hasTable('tbl_crm_participant_docs')) {
            Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
                $table->integer("stage_id")->after('crm_participant_id');
                $table->integer("document_type")->after('created')->default(0)->comment('1=service agreement 2=funding concent 3=final service agreement');
                $table->integer("document_signed")->after('document_type')->default(0)->comment('0=No 2=Yes');
                $table->string("envelope_id",250)->after('document_signed');
                $table->string("signed_file_path",250)->after('envelope_id');
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
        if (Schema::hasTable('tbl_crm_participant_stage_docs')) {
            Schema::dropIfExists('tbl_crm_participant_stage_docs');
        }
        if (Schema::hasTable('tbl_crm_participant_docs')) {
            Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
                $table->dropColumn("stage_id");
                $table->dropColumn("document_type");
                $table->dropColumn("document_signed");
                $table->dropColumn("envelope_id");
                $table->dropColumn("signed_file_path");
            });
        }
    }
}
