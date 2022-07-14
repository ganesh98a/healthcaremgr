<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssessmentDiagnosisAsAddSearchTerm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_diagnosis', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_need_assessment_diagnosis', 'search_term')) {
                $table->string('search_term',255)->comment("coming from SnowMed API")->after("diagnosis");
            }

            if (!Schema::hasColumn('tbl_need_assessment_diagnosis', 'sno_med_id')) {
                $table->unsignedInteger('sno_med_id')->comment("coming from SnoMed API, this id is not unique")->after("concept_id");
            }

            if (Schema::hasColumn('tbl_need_assessment_diagnosis', 'concept_id')) {
                $table->string('concept_id',255)->comment("conceptId+'_'+label")->change();
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
        Schema::table('tbl_need_assessment_diagnosis', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_need_assessment_diagnosis', 'search_term')) {
                $table->dropColumn('search_term');
            }
        });
    }
}
