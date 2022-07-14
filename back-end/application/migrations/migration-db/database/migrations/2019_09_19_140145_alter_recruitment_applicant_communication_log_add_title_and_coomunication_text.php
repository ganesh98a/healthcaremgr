<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantCommunicationLogAddTitleAndCoomunicationText extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_applicant_communication_log', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_communication_log', 'title')) {
                $table->string('title', 255)->after('log_type');
            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_communication_log', 'communication_text')) {
                $table->text('communication_text')->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_communication_log', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_communication_log', 'title')) {
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant_communication_log', 'communication_text')) {
                $table->dropColumn('communication_text');
            }
        });
    }

}
