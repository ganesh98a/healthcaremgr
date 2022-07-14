<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftExpensesAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_expenses_attachment')) {
        Schema::create('tbl_shift_expenses_attachment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shiftId');
            $table->unsignedInteger('shift_amendment_id');
            $table->string('receipt',200);
            $table->string('receipt_value',200);
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
        Schema::dropIfExists('tbl_shift_expenses_attachment');
    }
}
