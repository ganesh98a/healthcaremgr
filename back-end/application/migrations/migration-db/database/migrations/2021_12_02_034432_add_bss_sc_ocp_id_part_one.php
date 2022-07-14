<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBssScOcpIdPartOne extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement', 'sc_ocp_id')) {
                $table->unsignedInteger('sc_ocp_id')->comment('sc_ocp_id_for_migration')->after('id');
            }

        });
        Schema::table('tbl_sales_relation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sales_relation', 'sc_ocp_id')) {
                $table->unsignedInteger('sc_ocp_id')->comment('sc_ocp_id_for_migration')->after('id');
            }

        });
        Schema::table('tbl_goals_master', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_goals_master', 'sc_ocp_id')) {
                $table->unsignedInteger('sc_ocp_id')->comment('sc_ocp_id_for_migration')->after('id');
            }

        });
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_crm_participant_schedule_task', 'sc_ocp_id')) {
                $table->unsignedInteger('sc_ocp_id')->comment('sc_ocp_id_for_migration')->after('id');
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
            if (Schema::hasColumn('tbl_service_agreement', 'sc_ocp_id')) {
                 $table->dropColumn('sc_ocp_id');
            }
        });
        Schema::table('tbl_sales_relation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_relation', 'sc_ocp_id')) {
                 $table->dropColumn('sc_ocp_id');
            }
        });
        Schema::table('tbl_goals_master', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_goals_master', 'sc_ocp_id')) {
                 $table->dropColumn('sc_ocp_id');
            }
        });
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant_schedule_task', 'sc_ocp_id')) {
                 $table->dropColumn('sc_ocp_id');
            }
        });
    }
}
