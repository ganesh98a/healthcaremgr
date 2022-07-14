<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',200);
            $table->unsignedInteger('type');
            $table->unsignedInteger('category');
            $table->unsignedInteger('sub_category');
            $table->unsignedInteger('position');
            $table->unsignedInteger('employment_type');
            $table->unsignedInteger('salary_range');
            $table->unsignedInteger('is_salary_publish');
            $table->unsignedInteger('location');
            $table->unsignedInteger('requirement_docs');
            $table->unsignedInteger('template');
            $table->unsignedInteger('save_as')->comment('0 - Draft');
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
        Schema::dropIfExists('tbl_recruitment_job');
    }
}
