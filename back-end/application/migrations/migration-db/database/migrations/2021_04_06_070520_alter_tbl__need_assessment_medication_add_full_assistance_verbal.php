<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssessmentMedicationAddFullAssistanceVerbal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_need_assessment_medication')) {
            Schema::table('tbl_need_assessment_medication', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_need_assessment_medication', 'full_assistance_and_verbal')) {
                    $table->unsignedSmallInteger('full_assistance_and_verbal')->comment("0-not applicable , 1- full assistance, 2- verbal prompting")->after('reduce_concern');
                }
                if (!Schema::hasColumn('tbl_need_assessment_medication', 'tablets_liquid_oral')) {
                    $table->unsignedSmallInteger('tablets_liquid_oral')->after('full_assistance_and_verbal');
                }
                if (!Schema::hasColumn('tbl_need_assessment_medication', 'crushed_oral')) {
                    $table->unsignedSmallInteger('crushed_oral')->after('tablets_liquid_oral');
                }
                if (!Schema::hasColumn('tbl_need_assessment_medication', 'crushed_via_peg')) {
                    $table->unsignedSmallInteger('crushed_via_peg')->after('crushed_oral');
                }
                if (!Schema::hasColumn('tbl_need_assessment_medication', 'medication_vitamins_counter')){
                    $table->unsignedSmallInteger('medication_vitamins_counter')->comment("1- no, 2- yes")->after('crushed_via_peg');
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
        //
    }
}
