<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNeedAssessmentDiagnosis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_diagnosis', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_need_assessment_diagnosis', 'primary_disability')) {
                $table->tinyInteger('primary_disability')->default(0)->after("diagnosis")->comment("0=>No, 1=>Yes");
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
            if (Schema::hasColumn('tbl_need_assessment_diagnosis', 'primary_disability')) {
                $table->dropColumn('primary_disability');
            }
        });
    }
}
