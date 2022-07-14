<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNeedAssessmentHealthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_need_assessment_health', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
            $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');
            $table->unsignedSmallInteger('not_applicable')->comment("1- Not applicable, 2- Yes");
            $table->unsignedSmallInteger('diabetes')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
            $table->unsignedSmallInteger('epilepsy')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
            $table->unsignedSmallInteger('asthma')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
            $table->unsignedSmallInteger('dietry_requirements')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
            $table->unsignedSmallInteger('alergies')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
            $table->unsignedSmallInteger('bladder_bowel_care')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
            $table->unsignedSmallInteger('pressure_care')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
            $table->unsignedSmallInteger('stoma')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
            $table->string('other_label',100);
            $table->unsignedSmallInteger('other')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes without plan");
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
        Schema::dropIfExists('tbl_need_assessment_health');
    }
}
