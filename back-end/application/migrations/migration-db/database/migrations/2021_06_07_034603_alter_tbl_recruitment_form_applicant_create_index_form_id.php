<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentFormApplicantCreateIndexFormId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            $table->index('form_id', 'index_form_id');
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
            $table->dropIndex('index_form_id');
        });
    }
}
