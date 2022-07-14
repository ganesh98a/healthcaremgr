<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentFormApplicantAddNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_form_applicant')) {
            Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'notes')) {
                    $table->longText('notes')->nullable();
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
        //
    }
}
