<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantStageAttachmentColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'expiry_date')) {
                $table->dateTime('expiry_date')->nullable()->comment('Date document status was set to expired');
            }

            // updated_at 
            // the extra triggers ON UPDATE CURRENT_TIMESTAMP adds current timestamp if not included in INSERT/UPDATE
            if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'updated_at')) {
                $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            }
        });

        // Just update one comment
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'document_status')) {
                $table->unsignedSmallInteger('document_status')->default(0)->comment('0-Pending/Submitted,1-Successful/Valid,2-Rejected/Invalid,3-Expired')->change();
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
        // Just revert back to the old comment
        // Note: $table->comment(...)->change() wont work because it is not macroable
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'document_status')) {
                $table->unsignedSmallInteger('document_status')->default(0)->comment('0-Pending,1-Successful,2-Rejected')->change();
            }
        });

        // Drop inserted columns
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });

    }
}
