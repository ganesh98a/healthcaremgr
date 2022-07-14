<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceDisputeNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_finance_dispute_note')) {
        Schema::create('tbl_finance_dispute_note', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reason', 500);
            $table->string('raised', 50);
            $table->string('contact_name', 50);
            $table->string('contact_method', 50);
            $table->string('notes', 1000);
            $table->string('type', 30);
            $table->unsignedTinyInteger('status')->comment('0- Inactive /1 - Active')->default('1');
            $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_finance_dispute_note');
    }
}
