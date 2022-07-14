<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentTaskApplicantAsAddColumnStageLabelId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
          if (!Schema::hasColumn('tbl_recruitment_task_applicant', 'stage_label_id')) {
            $table->unsignedInteger('stage_label_id')->comment('tbl_recruitment_stage_label.id')->after('application_id');
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
        Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_recruitment_task_applicant', 'stage_label_id')) {
            $table->dropColumn('stage_label_id');
          }
        });
    
    }
}
