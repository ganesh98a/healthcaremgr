<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantReferenceAddStatusAndRelevantNote extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_applicant_reference')) {

            Schema::table('tbl_recruitment_applicant_reference', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant_reference', 'status')) {
                    $table->unsignedSmallInteger('status')->default(1)->commnet('1 - Approved/ 2 - Rejected')->after('phone');
                }
                if (!Schema::hasColumn('tbl_recruitment_applicant_reference', 'relevant_note')) {
                    $table->text('relevant_note')->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_applicant_reference', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_reference', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_reference', 'relevant_note')) {
                $table->dropColumn('relevant_note');
            }
        });
    }

}
