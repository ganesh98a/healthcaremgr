<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNeedAssessmentDiagnosisTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void 
     */
    public function up()
    {
        Schema::create('tbl_need_assessment_diagnosis', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
            $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');
            $table->bigInteger('concept_id')->comment('Unique id for diagnosis coming from API');
            $table->string('diagnosis',255);
            $table->unsignedSmallInteger('support_level')->comment("High- 1, Medium- 2, Low- 3");
            $table->unsignedSmallInteger('current_plan')->comment("1- yes, 2- No");
            $table->date('plan_end_date');
            $table->unsignedSmallInteger('impact_on_participant')->comment("Severe- 1, Moderate- 2, Mild- 3, Managed by medication- 4");
            $table->unsignedSmallInteger('archive')->comment("0- No, 1- Yes");
            $table->timestamp('created')->useCurrent();
            $table->unsignedInteger('created_by');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->unsignedInteger('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_need_assessment_diagnosis');
    }
}
