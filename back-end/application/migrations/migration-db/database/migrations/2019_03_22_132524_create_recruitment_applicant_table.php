<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_applicant')) {
            Schema::create('tbl_recruitment_applicant', function (Blueprint $table) {
                $table->increments('id');
                $table->string('firstname',30);
                $table->string('middlename',30);
                $table->string('lastname',30);
                $table->string('applicant_code',30);
                $table->unsignedTinyInteger('application_category');
                $table->unsignedTinyInteger('applicant_classification')->comment('skilled , non skilled etc');
                $table->datetime('date_applide');
                $table->unsignedTinyInteger('job_exeperiance')->comment('0=No/1=Yes');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->timestamp('lastupdate')->useCurrent();
                $table->unsignedTinyInteger('status')->comment('1- Active, 0- Inactive');
                $table->unsignedTinyInteger('currunt_stage');
                $table->unsignedTinyInteger('archive')->default('0');
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
        Schema::dropIfExists('tbl_recruitment_applicant');
    }
}
