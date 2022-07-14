<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantFormAddDateTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'start_datetime')) {
                $table->datetime('start_datetime')->nullable()->after('status');
            }
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'end_datetime')) {
                $table->datetime('end_datetime')->nullable()->after('start_datetime');
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
            if (Schema::hasColumn('tbl_recruitment_form_applicant', 'start_datetime')) {
                $table->dropColumn('start_datetime');
            }
            if (Schema::hasColumn('tbl_recruitment_form_applicant', 'end_datetime')) {
                $table->dropColumn('end_datetime');
            }
        });
    }
}
