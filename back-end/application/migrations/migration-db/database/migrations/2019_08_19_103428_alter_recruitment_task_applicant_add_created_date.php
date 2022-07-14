<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentTaskApplicantAddCreatedDate extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_task_applicant')) {
            Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
                $table->datetime('invitation_send_at')->default('0000-00-00 00:00:00');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_recruitment_task_applicant')) {
            Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
                $table->dropColumn('invitation_at');
            });
        }
    }

}
