<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnCrmParticipant extends Migration
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
                $table->unsignedTinyInteger('ndis_plan')->comment('1=Self Managed 2=Portal Managed 3=Plan Management Provider');              
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
       
        if(Schema::hasTable('tbl_crm_participant') && Schema::hasColumn('tbl_crm_participant', 'ndis_plan')) {
            Schema::table('tbl_crm_participant', function (Blueprint $table) {               
                $table->dropColumn('ndis_plan');
            });
            
          }
    }
}
