<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceDisputeNoteTable extends Migration
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
                $table->unsignedInteger('invoice_id')->after('reason');
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
        if (Schema::hasTable('tbl_finance_dispute_note')) {
          Schema::table('tbl_finance_dispute_note', function (Blueprint $table) {
            //  $table->unsignedInteger('invoice_id');
          });
        }
    }
}
