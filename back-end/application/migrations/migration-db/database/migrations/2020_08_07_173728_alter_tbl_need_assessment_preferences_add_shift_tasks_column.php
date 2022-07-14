<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssessmentPreferencesAddShiftTasksColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_need_assessment_preferences', 'in_home_shift_tasks')) {
                $table->text('in_home_shift_tasks')->nullable()->after('as_public_holiday');
            }
            if (!Schema::hasColumn('tbl_need_assessment_preferences', 'community_access_shift_tasks')) {
                $table->text('community_access_shift_tasks')->nullable()->after('in_home_shift_tasks');
            }
            if (!Schema::hasColumn('tbl_need_assessment_preferences', 'active_night_details')) {
                $table->text('active_night_details')->nullable()->after('community_access_shift_tasks');
            }
            if (!Schema::hasColumn('tbl_need_assessment_preferences', 'sleep_over_details')) {
                $table->text('sleep_over_details')->nullable()->after('active_night_details');
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
            if (Schema::hasColumn('tbl_need_assessment_preferences', 'in_home_shift_tasks')) {
                $table->dropColumn('in_home_shift_tasks');
            }
            if (Schema::hasColumn('tbl_need_assessment_preferences', 'community_access_shift_tasks')) {
                $table->dropColumn('community_access_shift_tasks');
            }
            if (Schema::hasColumn('tbl_need_assessment_preferences', 'active_night_details')) {
                $table->dropColumn('active_night_details');
            }
            if (Schema::hasColumn('tbl_need_assessment_preferences', 'sleep_over_details')) {
                $table->dropColumn('sleep_over_details');
            }
        });
    }
}
