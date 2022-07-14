<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_application')) {
            Schema::create('tbl_recruitment_application', function (Blueprint $table) {
                $table->increments('id');
                $table->string('application_category',50);
                $table->unsignedTinyInteger('status')->comment('1- Active, 0- Inactive');
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
        Schema::dropIfExists('tbl_recruitment_application');
    }
}
