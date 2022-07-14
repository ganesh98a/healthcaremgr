<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobRequirementDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job_requirement_docs', function (Blueprint $table) {
           $table->increments('id');
           $table->string('title',200);
           $table->unsignedInteger('archive')->comment('0- not/ 1 - archive');
           $table->dateTime('created')->default('0000-00-00 00:00:00');
           $table->timestamp('updated')->useCurrent();
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_job_requirement_docs');
    }
}
