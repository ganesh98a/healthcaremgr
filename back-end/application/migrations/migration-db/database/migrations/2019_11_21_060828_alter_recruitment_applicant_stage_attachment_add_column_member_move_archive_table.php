<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantStageAttachmentAddColumnMemberMoveArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_stage_attachment')) {
            Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','member_move_archive')){
                    $table->unsignedSmallInteger('member_move_archive')->default(0)->nullable()->comment('0-already archive,1-active attachment archive when applicant move on member table');
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
        if (Schema::hasTable('tbl_recruitment_applicant_stage_attachment')) {
            Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_applicant_stage_attachment','member_move_archive')){
                    $table->dropColumn('member_move_archive');
                } 
            });

        }
    }
}
