<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableCrmParticipantPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_crm_participant_plan')) {
            Schema::table('tbl_crm_participant_plan', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_crm_participant_plan', 'archive')) {
                    $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data');
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
        if (Schema::hasTable('tbl_crm_participant_plan')) {
            Schema::table('tbl_crm_participant_plan', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_crm_participant_plan', 'archive')) {
                    $table->dropColumn('archive');
                }
            });

        }

    }
}
