<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantAddNewColumnNdisService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_crm_participant')) {
            Schema::table('tbl_crm_participant', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_crm_participant', 'total_ndis_service')) {
                    $table->string('total_ndis_service',100);
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
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
            if(Schema::hasTable('tbl_crm_participant') && Schema::hasColumn('tbl_crm_participant', 'total_ndis_service')) {
               $table->dropColumn('total_ndis_service');
            }
        });
    }
}
