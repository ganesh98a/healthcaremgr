<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaskIdTblRecruitmentFormApplicant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'task_id')) {
                $table->unsignedInteger('task_id')->nullable()->comment('tbl_recruitment_task')->after('form_id');
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
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {  
            if (Schema::hasColumn('tbl_recruitment_form_applicant', 'task_id')) {
                $table->dropColumn('task_id');
            }
        });
    }
}
