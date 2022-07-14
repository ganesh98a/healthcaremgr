<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRenameFinanceDisputeNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_dispute_note')) {
        Schema::table('tbl_finance_dispute_note', function (Blueprint $table) {
            Schema::rename('tbl_finance_dispute_note', 'tbl_plan_management_dispute_note');
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
      if (Schema::hasTable('tbl_plan_management_dispute_note')) {
        Schema::table('tbl_finance_dispute_note', function (Blueprint $table) {
            Schema::rename('tbl_plan_management_dispute_note', 'tbl_finance_dispute_note');
        });
      }
    }
}
