<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftGoalNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_goal_notes')) {
            Schema::create('tbl_shift_goal_notes', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('shift_id')->comment('tbl_shift.id');
                $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');

                $table->text('task_taken')->nullable();
                $table->text('worked_well')->nullable();
                $table->text('done_better')->nullable();

                $table->dateTime('created')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->dateTime('updated')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
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
        Schema::dropIfExists('tbl_shift_goal_notes');
    }
}
