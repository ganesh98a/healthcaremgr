<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantReferenceAddReferenceCheckColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_reference')) {
            Schema::table('tbl_recruitment_applicant_reference', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_applicant_reference', 'written_reference')) {
                    $table->unsignedSmallInteger('written_reference')->default(0)->comment("0 - No/1 - Yes")->after('relevant_note');
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
        if (Schema::hasTable('tbl_recruitment_applicant_reference')) {
            Schema::table('tbl_recruitment_applicant_reference', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant_reference', 'written_reference')) {
                    $table->dropColumn('written_reference');
                }
            });
        }   
    }
}
