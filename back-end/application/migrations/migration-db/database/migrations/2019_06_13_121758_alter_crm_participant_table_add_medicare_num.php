<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantTableAddMedicareNum extends Migration
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
          $table->string('medicare_num',15)->nullable()->change();
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
      if (Schema::hasTable('tbl_crm_participant') && Schema::hasColumn('tbl_crm_participant','medicare_num')) {
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
              $table->dropColumn('medicare_num');
        });
      }
    }
}
