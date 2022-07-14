<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_notes')) {
            Schema::create('tbl_shift_notes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId');
                $table->unsignedInteger('adminId');
                $table->string('title',200);
                $table->text('notes');
                $table->unsignedInteger('archive')->comment('0- not/ 1 - archive');
                $table->timestamp('created')->useCurrent();       
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
        Schema::dropIfExists('tbl_shift_notes');
    }
}
