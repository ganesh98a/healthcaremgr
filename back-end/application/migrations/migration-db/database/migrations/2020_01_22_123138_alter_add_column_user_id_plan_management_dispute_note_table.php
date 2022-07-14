<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnUserIdPlanManagementDisputeNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_plan_management_dispute_note')) {
          Schema::table('tbl_plan_management_dispute_note', function (Blueprint $table) {
              $table->unsignedInteger('user_id');
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
        Schema::table('tbl_plan_management_dispute_note', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_plan_management_dispute_note', 'user_id')) {
              $table->dropColumn('user_id');
          }
        });
    }
}
