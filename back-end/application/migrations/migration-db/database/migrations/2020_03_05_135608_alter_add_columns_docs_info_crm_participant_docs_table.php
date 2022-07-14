<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnsDocsInfoCrmParticipantDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
          if (!Schema::hasColumn('tbl_crm_participant_docs','behavioral_support_doc') && !Schema::hasColumn('tbl_crm_participant_docs','other_relevent_doc')  && !Schema::hasColumn('tbl_crm_participant_docs','notes ')  && !Schema::hasColumn('tbl_crm_participant_docs','legal_isues_doc') ) {
            $table->string('behavioral_support_doc',200)->nullable();
            $table->string('other_relevent_doc',200)->nullable();
            $table->string('legal_isues_doc',200)->nullable();
            $table->string('notes',200)->nullable();

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
        Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_crm_participant_docs','behavioral_support_doc') && Schema::hasColumn('tbl_crm_participant_docs','other_relevent_doc')  && Schema::hasColumn('tbl_crm_participant_docs','notes ')  && Schema::hasColumn('tbl_crm_participant_docs','legal_isues_doc') ) {
            $table->dropColumn('behavioral_support_doc');
            $table->dropColumn('other_relevent_doc');
            $table->dropColumn('legal_isues_doc');
            $table->dropColumn('notes');
          }
        });
    }
}
