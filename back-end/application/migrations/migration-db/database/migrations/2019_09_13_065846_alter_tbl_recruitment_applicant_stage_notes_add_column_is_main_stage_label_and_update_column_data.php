<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantStageNotesAddColumnIsMainStageLabelAndUpdateColumnData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_stage_notes')) {
            Schema::table('tbl_recruitment_applicant_stage_notes', function (Blueprint $table) {
                
                
                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_notes','is_main_stage_label')) {
                    $table->unsignedSmallInteger('is_main_stage_label')->default(0)->comment('0- sub stage and stage column value is primary key tbl_recruitment_stage,1 - is main stage and stage column value is primary key tbl_recruitment_stage_label');
                }
                if (!Schema::hasColumn('tbl_recruitment_applicant_stage_notes','recruiterId')) {
                    $table->unsignedInteger('recruiterId')->default(0)->comment('notes added by this recuiter');
                }
                if (Schema::hasColumn('tbl_recruitment_applicant_stage_notes','stage')) {
                    $table->unsignedInteger('stage')->default(0)->change();
                }

                if (Schema::hasColumn('tbl_recruitment_applicant_stage_notes','notes')) {
                    $table->text('notes')->nullable()->change();
                }

                if (Schema::hasColumn('tbl_recruitment_applicant_stage_notes','status')) {
                    $table->dropColumn('status');
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
        if (Schema::hasTable('tbl_recruitment_applicant_stage_notes')) {
            Schema::table('tbl_recruitment_applicant_stage_notes', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant_stage_notes','is_main_stage_label')) {
                    $table->dropColumn('is_main_stage_label');
                }
                if (Schema::hasColumn('tbl_recruitment_applicant_stage_notes','recruiterId')) {
                    $table->dropColumn('recruiterId');
                }
            });
        }
    }
}
