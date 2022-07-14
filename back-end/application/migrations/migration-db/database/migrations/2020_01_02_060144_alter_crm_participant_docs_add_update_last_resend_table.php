<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantDocsAddUpdateLastResendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
			if (!Schema::hasColumn('tbl_crm_participant_docs', 'updated')) {                
				$table->timestamp('updated')->after('created');
            }
			if (!Schema::hasColumn('tbl_crm_participant_docs', 'resend_docusign')) {
                $table->timestamp('resend_docusign')->after('updated');
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
            if (Schema::hasColumn('tbl_crm_participant_docs', 'updated')) {
                $table->dropColumn('updated');
            }
			if (Schema::hasColumn('tbl_crm_participant_docs', 'resend_docusign')) {
                $table->dropColumn('resend_docusign');
            }
        });
    }
}
