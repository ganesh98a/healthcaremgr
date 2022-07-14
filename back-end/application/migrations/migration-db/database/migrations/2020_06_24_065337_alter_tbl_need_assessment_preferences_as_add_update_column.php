<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssessmentPreferencesAsAddUpdateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_need_assessment_preferences', 'cancel_shift')) {
                $table->unsignedSmallInteger('cancel_shift')->comment("checked- 1, un-checked- 0 (come under `Vacant Shifts`)")->after('shadow_shift');
            } 

            if (!Schema::hasColumn('tbl_need_assessment_preferences', 'worker_available')) {
                $table->unsignedSmallInteger('worker_available')->comment("checked- 1, un-checked- 0 (come under `Vacant Shifts`)")->after('cancel_shift');
            }

            if (Schema::hasColumn('tbl_need_assessment_preferences', 'contact_shift')) {
                $table->dropColumn('contact_shift');
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
            //
        });
    }
}
