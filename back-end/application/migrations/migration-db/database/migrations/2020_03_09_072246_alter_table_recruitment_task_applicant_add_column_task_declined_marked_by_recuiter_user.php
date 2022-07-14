<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentTaskApplicantAddColumnTaskDeclinedMarkedByRecuiterUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_recruitment_task_applicant')){
            Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_recruitment_task_applicant','is_decline_mark_by_recruiter_user')){				
					$table->unsignedInteger('is_decline_mark_by_recruiter_user')->default(0)->comment('0 it depend on status column value if status value 2 then decline by applicant otherwhise decline by recuiter id mention here.')->after('status');
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
        if(Schema::hasTable('tbl_recruitment_task_applicant')){
            Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_task_applicant','is_decline_mark_by_recruiter_user')){				
					$table->dropColumn('is_decline_mark_by_recruiter_user');
				}
            });
        }
    }
}
