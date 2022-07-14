<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhysicalAidsVerbalColumnTblNeedAssessmentMealtime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_mealtime', function (Blueprint $table) {
           
            if (!Schema::hasColumn('tbl_need_assessment_mealtime', 'require_assistance_plan')){
                $table->unsignedInteger('require_assistance_plan')->default(0)->nullable()->after('assistance_plan_requirement');
            }
            if (!Schema::hasColumn('tbl_need_assessment_mealtime', 'physical_assistance')){
                $table->unsignedInteger('physical_assistance')->default(0)->nullable()->after('require_assistance_plan');
            }
            if (!Schema::hasColumn('tbl_need_assessment_mealtime', 'physical_assistance_desc')){
                $table->text('physical_assistance_desc')->nullable()->after('physical_assistance');
            }
            if (!Schema::hasColumn('tbl_need_assessment_mealtime', 'verbal_prompting')){
                $table->unsignedInteger('verbal_prompting')->nullable()->after('physical_assistance_desc');
            }
            if (!Schema::hasColumn('tbl_need_assessment_mealtime', 'verbal_prompting_desc')){
                $table->text('verbal_prompting_desc')->nullable()->after('verbal_prompting');
            }
            if (!Schema::hasColumn('tbl_need_assessment_mealtime', 'aids')){
                $table->unsignedInteger('aids')->default(0)->nullable()->after('verbal_prompting_desc');
            }
            if (!Schema::hasColumn('tbl_need_assessment_mealtime', 'aids_desc')){
                $table->text('aids_desc')->nullable()->after('aids');
            }           
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_need_assessment_mealtime', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_need_assessment_mealtime', 'require_assistance_plan')) {
                $table->dropColumn('require_assistance_plan');
            }
            if (Schema::hasColumn('tbl_need_assessment_mealtime', 'physical_assistance')) {
                $table->dropColumn('physical_assistance');
            }
            if (Schema::hasColumn('tbl_need_assessment_mealtime', 'physical_assistance_desc')) {
                $table->dropColumn('physical_assistance_desc');
            }
            if (Schema::hasColumn('tbl_need_assessment_mealtime', 'verbal_prompting')) {
                $table->dropColumn('verbal_prompting');
            }
            if (Schema::hasColumn('tbl_need_assessment_mealtime', 'verbal_prompting_desc')) {
                $table->dropColumn('verbal_prompting_desc');
            }
            if (Schema::hasColumn('tbl_need_assessment_mealtime', 'aids')) {
                $table->dropColumn('aids');
            }
            if (Schema::hasColumn('tbl_need_assessment_mealtime', 'aids_desc')) {
                $table->dropColumn('aids_desc');
            }          
          
        });
    }
}
