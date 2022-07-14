<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantWorkAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_applicant_work_area', function (Blueprint $table) {
            $table->increments('id');
			$table->string('work_area',200)->nullable();
			$table->unsignedSmallInteger('status')->comment('1- Active, 0- Inactive');
			$table->unsignedSmallInteger('archived')->comment('1- archived, 0- Default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_applicant_work_area');
    }
}
