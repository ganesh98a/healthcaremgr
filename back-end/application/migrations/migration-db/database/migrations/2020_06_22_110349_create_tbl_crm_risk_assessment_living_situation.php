<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCrmRiskAssessmentLivingSituation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_crm_risk_assessment_living_situation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('risk_assessment_id')->unsigned()->comment("tbl_crm_risk_assessment.id");

            $table->unsignedInteger('living_situation')->comment("1 - Lives Alone/2 - lives with family/3 - lives with other/4 - SDA");
            $table->string('living_situation_agency', 255)->nullble();

            $table->unsignedInteger('informal_support')->comment("1 - No/2 - Yes");
            $table->string('informal_support_describe', 255)->nullble();

            $table->unsignedInteger('lack_of_informal_support')->comment("1 - No/2 - Yes");
            $table->string('lack_of_informal_support_describe', 255)->nullble();

            $table->dateTime('created');
            $table->dateTime('updated');
            $table->unsignedSmallInteger('archive')->comment("0 - No/2 - Yes");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_crm_risk_assessment_living_situation');
    }

}
