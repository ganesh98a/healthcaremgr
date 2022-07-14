<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddfieldTblNeedAssessmentHealth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_need_assessment_health')) {
            Schema::table('tbl_need_assessment_health', function (Blueprint $table) {

                if (!Schema::hasColumn('tbl_need_assessment_health', 'peg_pej')) {
                    $table->unsignedSmallInteger('peg_pej')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes with other supports, 4- I don't require staff support with this")
                        ->after('stoma');
                }
                if (!Schema::hasColumn('tbl_need_assessment_health', 'anaphylaxis')) {
                    $table->unsignedSmallInteger('anaphylaxis')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes with other supports, 4- I don't require staff support with this")
                        ->after('peg_pej');
                }
                if (!Schema::hasColumn('tbl_need_assessment_health', 'breath_assist')) {
                    $table->unsignedSmallInteger('breath_assist')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes with other supports, 4- I don't require staff support with this")
                        ->after('anaphylaxis');
                }
                if (!Schema::hasColumn('tbl_need_assessment_health', 'mental_health')) {
                    $table->unsignedSmallInteger('mental_health')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes with other supports, 4- I don't require staff support with this")
                        ->after('breath_assist');
                }
                if (!Schema::hasColumn('tbl_need_assessment_health', 'nursing_service')) {
                    $table->unsignedSmallInteger('nursing_service')->comment("0- Not applicable, 1- No, 2- Yes with plan, 3- Yes with other supports, 4- I don't require staff support with this")
                        ->after('mental_health');
                }

                if (!Schema::hasColumn('tbl_need_assessment_health', 'nursing_service_reason')) {
                    $table->text('nursing_service_reason')->nullable()->comment('Nursing service reason')->after('nursing_service');
                }
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

    }
}
