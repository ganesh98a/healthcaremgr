<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnCategoryDocParticipantDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participant_docs', function (Blueprint $table) {
            DB::unprepared("ALTER TABLE `tbl_participant_docs` MODIFY `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ; ");
            DB::unprepared("ALTER TABLE `tbl_participant_docs` MODIFY `updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ; ");
            DB::unprepared("ALTER TABLE `tbl_participant_docs` MODIFY `resend_docusign` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ; ");
            DB::unprepared("ALTER TABLE `tbl_participant_docs` MODIFY `type` INT(11) NOT NULL COMMENT '' ; ");
            if (!Schema::hasColumn('tbl_participant_docs', 'doc_category')) {
                $table->unsignedInteger('doc_category')->after('type')->comment('0- Intake Plan Docs, 1- NDIS Plan Docs, 2- Behavioral Support Plan Docs, 3- Manage Attachments Docs');
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
        Schema::table('tbl_participant_docs', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participant_docs', 'stage_id')) {
                $table->dropColumn('doc_category');
            }
        });
    }
}
