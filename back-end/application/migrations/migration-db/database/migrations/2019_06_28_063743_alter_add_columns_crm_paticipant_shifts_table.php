<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnsCrmPaticipantShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant_shifts')) {
        Schema::table('tbl_crm_participant_shifts', function (Blueprint $table) {
          $table->dropColumn('end_date');
          $table->dropColumn('title');
          $table->dropColumn('start_date');
          $table->timestamp('shift_date')->nullable();
          $table->timestamp('start_time')->nullable();
          $table->timestamp('end_time')->nullable();
          $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
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
      if(Schema::hasTable('tbl_crm_participant_shifts') && Schema::hasColumn('tbl_crm_participant_shifts', 'end_date') && Schema::hasColumn('tbl_crm_participant_shifts', 'title') && Schema::hasColumn('tbl_crm_participant_shifts', 'start_date')) {
        Schema::table('tbl_crm_participant_shifts', function (Blueprint $table) {
          $table->dropColumn('end_date');
          $table->dropColumn('start_time');
          $table->dropColumn('end_time');
          $table->dropColumn('updated');
          $table->dropColumn('start_date');
          $table->dropColumn('shift_date');
        });
      }
    }
}
