<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNeedAssessmentNutritionalSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_need_assessment_ns', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
            $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');

            $table->unsignedSmallInteger('support_with_eating')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->text('support_desc')->nullable();
            $table->unsignedSmallInteger('risk_aspiration')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->unsignedSmallInteger('risk_choking')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->unsignedSmallInteger('aspiration_food')->default(0)->nullable();
            $table->text('aspiration_food_desc')->nullable();
            $table->unsignedSmallInteger('aspiration_fluids')->default(0)->nullable();
            $table->text('aspiration_fluids_desc')->nullable();

            $table->unsignedInteger('choking_food')->default(0)->nullable();
            $table->text('choking_food_desc')->nullable();
            $table->unsignedInteger('choking_fluids')->default(0)->nullable();
            $table->text('choking_fluids_desc')->nullable();

            $table->text('food_preferences_desc')->nullable();

            $table->unsignedSmallInteger('peg_assistance_plan')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->unsignedSmallInteger('pej_assistance_plan')->comment("0- Not applicable, 1- No, 2- Yes");

            $table->unsignedSmallInteger('archive')->comment("0- No, 1- Yes");
            
            $table->timestamp('created')->useCurrent();            
            $table->unsignedInteger('created_by');            
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));            
            $table->unsignedInteger('updated_by');
        });

        Schema::create('tbl_ns_food_preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('na_nutritional_support_id')->comment("tbl_need_assessment_ns.id");
            $table->unsignedSmallInteger('food_preferences_ref_id')->comment("tbl_references");
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
        Schema::dropIfExists('tbl_need_assessment_ns');
        Schema::dropIfExists('tbl_ns_food_preferences');
    }
}
