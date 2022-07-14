<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssessmentAssistanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_assessment_assistance')) {
            Schema::create('tbl_assessment_assistance', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 200);
                $table->string('key_name', 200);
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('archive')->comment('1- delete');
            });
        }
        
        if (Schema::hasTable('tbl_assessment_assistance')) {
            $seeder = new AssessmentAssistance();
            $seeder->run();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_assessment_assistance');
    }
}
