<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssessmentMealtimeAddReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_mealtime', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_need_assessment_mealtime', 'assistance_plan_requirement')) {
                $table->text('assistance_plan_requirement')->nullable()->comment('mealtime_assistance_plan requirement detail')->after('mealtime_assistance_plan');
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
            if (Schema::hasColumn('tbl_need_assessment_mealtime', 'assistance_plan_requirement')) {
                $table->dropColumn('assistance_plan_requirement');
            }
        });
    }
}
