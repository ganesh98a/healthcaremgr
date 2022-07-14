<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTblNeedAssessmentPreferencesAddNotApplicable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_need_assessment_preferences', 'not_applicable')) {
                $table->tinyInteger('not_applicable')->nullable()->default(0);
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
        Schema::table('tbl_need_assessment_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_need_assessment_preferences', 'not_applicable')) {
                $table->dropColumn('not_applicable');
            }
        });
    }
}
