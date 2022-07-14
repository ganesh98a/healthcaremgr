<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftBreak extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_break')) {
            Schema::create('tbl_shift_break', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shift_id')->comment('tbl_shift.id');
                $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('break_category')->comment('1 = scheduled, 2 = actual');
                $table->unsignedInteger('break_type')->nullable()->comment('tbl_references.id');
                $table->foreign('break_type')->references('id')->on('tbl_references')->onDelete(DB::raw('SET NULL'));

                $table->dateTime('start_datetime')->nullable();
                $table->dateTime('end_datetime')->nullable();
                $table->text('duration')->nullable();
                $table->unsignedInteger('duration_int')->nullable();

                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->dateTime('created')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->dateTime('updated')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_shift_break', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_break', 'shift_id')) {
                $table->dropForeign(['shift_id']);
            }
            if (Schema::hasColumn('tbl_shift_break', 'break_type')) {
                $table->dropForeign(['break_type']);
            }
            if (Schema::hasColumn('tbl_shift_break', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_shift_break', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_shift_break');
    }
}
