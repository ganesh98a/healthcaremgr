<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnManagerNameCrmNdisPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_crm_ndis_plan')) {
            Schema::table('tbl_crm_ndis_plan', function (Blueprint $table) {
                $table->renameColumn('manager_name', 'manager_plan');
                $table->unsignedInteger('crm_participant_id')->after('id');              
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
        if(Schema::hasTable('tbl_crm_ndis_plan') && Schema::hasColumn('tbl_crm_ndis_plan', 'manager_name') && Schema::hasColumn('tbl_crm_ndis_plan', 'crm_participant_id')) {
        Schema::table('tbl_crm_ndis_plan', function (Blueprint $table) {
            $table->renameColumn('manager_plan', 'manager_name');
            $table->dropColumn('crm_participant_id');
        });
        }
      }
}
