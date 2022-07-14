<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNeedAssessmentEquipmentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_need_assessment_equipment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
            $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');
            $table->unsignedSmallInteger('not_applicable')->comment("1- Not applicable, 2- Yes");
            $table->unsignedSmallInteger('walking_stick')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->unsignedSmallInteger('wheel_chair')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->string('model_brand',150);
            $table->unsignedSmallInteger('shower_chair')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->unsignedSmallInteger('transfer_aides')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->unsignedSmallInteger('daily_safety_aids')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->text('daily_safety_aids_description');
            $table->unsignedSmallInteger('walking_frame')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->unsignedSmallInteger('type')->comment("0- Not applicable, 1- Electric, 2- Motorised");
            $table->double('weight');
            $table->unsignedSmallInteger('toilet_chair')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->unsignedSmallInteger('hoist_sling')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->text('hoist_sling_description'); 
            $table->unsignedSmallInteger('other')->comment("0- Not applicable, 1- No, 2- Yes");
            $table->text('other_description');            
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
        Schema::dropIfExists('tbl_need_assessment_equipment');
    }
}
