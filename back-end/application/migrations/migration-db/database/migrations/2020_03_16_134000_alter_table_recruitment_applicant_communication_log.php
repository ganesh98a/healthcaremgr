<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentApplicantCommunicationLog extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_applicant_communication_log')) {
            Schema::table('tbl_recruitment_applicant_communication_log', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant_communication_log', 'log_type_section_key')) {
                    $table->string('log_type_section_key', 200)->nullable()->default('')->comment('group_interview_invitation/cab_day_docment_resend_sms/cab_day_docment_resend_email/group_docment_resend_email');
                }
            });
            if (Schema::hasColumn('tbl_recruitment_applicant_communication_log', 'log_type_section_key')) {
                DB::table('tbl_recruitment_applicant_communication_log')->where('log_type', '2')->where('title', 'CAB day interview invitation')->update(array('log_type_section_key' => 'cab_day_interview_invitation'));
                DB::table('tbl_recruitment_applicant_communication_log')->where('log_type', '2')->where('title', 'Group interview invitation')->update(array('log_type_section_key' => 'group_interview_invitation'));
                DB::table('tbl_recruitment_applicant_communication_log')->where('log_type', '1')->where('title', 'CAB day interview invitation')->update(array('log_type_section_key' => 'cab_day_docment_resend_sms', 'title' => 'CAB day interview DocuSign document resend by sms'));
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_recruitment_applicant_communication_log')) {
            Schema::table('tbl_recruitment_applicant_communication_log', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant_communication_log', 'log_type_section_key')) {
                    $table->dropColumn('log_type_section_key');
                }
                //
            });
        }
    }

}
