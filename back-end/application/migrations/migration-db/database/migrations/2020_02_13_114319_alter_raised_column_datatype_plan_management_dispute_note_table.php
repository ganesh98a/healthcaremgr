<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRaisedColumnDatatypePlanManagementDisputeNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_plan_management_dispute_note', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_plan_management_dispute_note','raised')) {
            DB::unprepared("ALTER TABLE `tbl_plan_management_dispute_note`
                CHANGE `raised` `raised` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ");
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
        Schema::table('tbl_plan_management_dispute_note', function (Blueprint $table) {
            //
        });
    }
}
