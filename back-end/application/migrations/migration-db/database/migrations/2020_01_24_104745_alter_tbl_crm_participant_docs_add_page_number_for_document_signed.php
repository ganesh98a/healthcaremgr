<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmParticipantDocsAddPageNumberForDocumentSigned extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_crm_participant_docs')) {
            Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_crm_participant_docs','docu_signed_page_number')) {
                    $table->unsignedInteger('docu_signed_page_number')->default(0)->nullable();
                }
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
        if (Schema::hasTable('tbl_crm_participant_docs')) {
            Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_crm_participant_docs','docu_signed_page_number')) {
                    $table->dropColumn('docu_signed_page_number');
                }
            });
          }
    }
}
