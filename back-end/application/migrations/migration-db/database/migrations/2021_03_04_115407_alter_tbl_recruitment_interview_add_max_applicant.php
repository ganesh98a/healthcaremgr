<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentInterviewAddMaxApplicant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_interview', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_interview', 'interview_duration')) {
                $table->text('interview_duration', 255)->nullable()->after('interview_end_datetime');
            }
            if (!Schema::hasColumn('tbl_recruitment_interview', 'max_applicant')) {
                $table->unsignedInteger('max_applicant')->nullable()->after('description');
            }
            if (!Schema::hasColumn('tbl_recruitment_interview', 'invite_type')) {
                $table->unsignedInteger('invite_type')->nullable()->comment('1- Quiz , 2-Meeting invite')->after('max_applicant');
            }
            if (!Schema::hasColumn('tbl_recruitment_interview', 'form_id')) {
                $table->unsignedInteger('form_id')->nullable()->comment('tbl_recruitment_form')->after('invite_type');
            }
            if (!Schema::hasColumn('tbl_recruitment_interview', 'meeting_link')) {
                $table->longText('meeting_link')->nullable()->after('form_id');
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
        Schema::table('tbl_recruitment_interview', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_interview', 'max_applicant')) {
                $table->dropColumn('max_applicant');
            }
            if (Schema::hasColumn('tbl_recruitment_interview', 'invite_type')) {
                $table->dropColumn('invite_type');
            }
            if (Schema::hasColumn('tbl_recruitment_interview', 'form_id')) {
                $table->dropColumn('form_id');
            }
            if (Schema::hasColumn('tbl_recruitment_interview', 'meeting_link')) {
                $table->dropColumn('meeting_link');
            }
        });
    }
}
