<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnModifiedRenewedStatusCrmParticipantTable extends Migration
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
          $table->unsignedInteger('modified_renewed_status')->default(0)->comment("1=modified 2=renewed");
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
      if(Schema::hasTable('tbl_crm_participant') && Schema::hasColumn('tbl_crm_participant', 'modified_renewed_status') ) {
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
            $table->dropColumn('modified_renewed_status');
        });
      }
    }
}
