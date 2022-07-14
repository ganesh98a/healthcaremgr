<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantGroupOrCabInterviewDetailAddCabCertificateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail','cab_certificate_status')){
                $table->unsignedSmallInteger('cab_certificate_status')->default('0')->comment("0 -pending / 1 - successfull")->after('quiz_status');
            }
			
			if(!Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail','genrate_cab_certificate')){
                $table->unsignedSmallInteger('genrate_cab_certificate')->default('0')->comment("0-pending/1-yes/2-No")->after('cab_certificate_status');
            }
			
			if(!Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail','email_cab_certificate')){
                $table->unsignedSmallInteger('email_cab_certificate')->default('0')->comment("0-pending/1-yes/2-No")->after('genrate_cab_certificate');
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
        Schema::table('tbl_recruitment_applicant_group_or_cab_interview_detail', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail','cab_certificate_status')){
                $table->dropColumn('cab_certificate_status');
            }
			
			if(Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail','genrate_cab_certificate')){
                $table->dropColumn('genrate_cab_certificate');
            }
			
			if(Schema::hasColumn('tbl_recruitment_applicant_group_or_cab_interview_detail','email_cab_certificate')){
                $table->dropColumn('email_cab_certificate');
            }
        });
    }
}
