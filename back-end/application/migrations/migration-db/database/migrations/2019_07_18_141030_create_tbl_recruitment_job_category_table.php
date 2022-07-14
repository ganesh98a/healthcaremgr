<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job_category', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->unsigned()->default(0);
            $table->string('description',255);
            $table->string('name', 150);
            $table->unsignedTinyInteger('archive')->default(0)->comment('1- Delete');
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
        Schema::dropIfExists('tbl_recruitment_job_category');
    }
}
