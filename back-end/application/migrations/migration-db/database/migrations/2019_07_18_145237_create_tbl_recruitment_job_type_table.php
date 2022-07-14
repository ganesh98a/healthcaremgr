<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job_type', function (Blueprint $table) {
           $table->increments('id');
            $table->string('title', 150);
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
        Schema::dropIfExists('tbl_recruitment_job_type');
    }
}
