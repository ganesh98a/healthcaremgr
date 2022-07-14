<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentTaskApplicantAddTokenEmail extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_task_applicant')) {
            Schema::table('tbl_recruitment_task_applicant', function (Blueprint $table) {
                $table->text('token_email');
                $table->dateTime('invitation_accepted_at')->default('0000-00-00 00:00:00');
                $table->dateTime('invitation_cancel_at')->default('0000-00-00 00:00:00');
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
                $table->dropColumn('token_email');
                $table->dropColumn('invitation_accepted_at');
                $table->dropColumn('invitation_cancel_at');
            });
        }
    }

}
