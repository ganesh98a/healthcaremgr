<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantDoc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_applicant_doc')) {
            Schema::create('tbl_recruitment_applicant_doc', function (Blueprint $table) {
                $table->increments('id');
                $table->string('document_name',100);
                $table->string('document_path',100);
                $table->unsignedInteger('applicant_id');
                $table->unsignedTinyInteger('document_type')->comment('1=Resume/2=cover letter/3=Qualification/4=First Aid');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_recruitment_applicant_doc');
    }
}
