<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBssOcpIdPartOne extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement', 'bss_ocp_id')) {
                $table->unsignedInteger('bss_ocp_id')->comment('bss_ocp_id_for_migration')->after('id');
            }

        });
        Schema::table('tbl_service_agreement_items', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement_items', 'bss_ocp_id')) {
                $table->unsignedInteger('bss_ocp_id')->comment('bss_ocp_id_for_migration')->after('id');
            }

        });
        Schema::table('tbl_goals_master', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_goals_master', 'bss_ocp_id')) {
                $table->unsignedInteger('bss_ocp_id')->comment('bss_ocp_id_for_migration')->after('id');
            }

        });
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'bss_ocp_id')) {
                $table->unsignedInteger('bss_ocp_id')->comment('bss_ocp_id_for_migration')->after('id');
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
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement', 'bss_ocp_id')) {
                 $table->dropColumn('bss_ocp_id');
            }
        });
        Schema::table('tbl_service_agreement_items', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement_items', 'bss_ocp_id')) {
                 $table->dropColumn('bss_ocp_id');
            }
        });
        Schema::table('tbl_goals_master', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_goals_master', 'bss_ocp_id')) {
                 $table->dropColumn('bss_ocp_id');
            }
        });
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'bss_ocp_id')) {
                 $table->dropColumn('bss_ocp_id');
            }
        });
    }
}
