<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantTableAddIntakeType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        if (Schema::hasTable('tbl_crm_participant') && !Schema::hasColumn('tbl_crm_participant','intake_type')) {
            Schema::table('tbl_crm_participant', function (Blueprint $table) {             
              $table->unsignedTinyInteger('intake_type')->index()->nullable()->default(1)->comment('1- new, 2- rejected,3- renewed');
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
      
        if(Schema::hasTable('tbl_crm_participant') && Schema::hasColumn('tbl_crm_participant', 'intake_type')) {
            Schema::table('tbl_crm_participant', function (Blueprint $table) {
                $table->dropColumn('intake_type');
            });
          }
    }
}
