<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentPositionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_position')) {
        Schema::create('tbl_recruitment_position', function (Blueprint $table) {
            $table->increments('id');
            $table->string('position',100);
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
        Schema::dropIfExists('tbl_recruitment_position');
    }
}
