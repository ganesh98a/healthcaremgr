<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftGoalTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_goal_tracking')) {
            Schema::create('tbl_shift_goal_tracking', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('shift_id')->comment('tbl_shift.id');
                $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');

                $table->unsignedInteger('goal_id')->nullable()->comment('tbl_shift.id');
                $table->foreign('goal_id')->references('id')->on('tbl_goals_master')->onUpdate('cascade')->onDelete('cascade');

                $table->unsignedInteger('goal_type')->comment('1-Not Attempted:Not relevant to this shift,
                     2-Not Attempted:Customers Choice, 3-Verbal Prompt, 4-Physical Assistance, Independent');
                $table->text('snapshot');
                $table->unsignedInteger('created_user_type')->comment('type of user Created by 1-admin, 2-member');

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
        Schema::table('tbl_shift_goal_tracking', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_goal_tracking', 'goal_id')) {
                $table->dropForeign(['goal_id']);
            }
            if (Schema::hasColumn('tbl_shift_goal_tracking', 'shift_id')) {
                $table->dropForeign(['shift_id']);
            }
        });
        Schema::dropIfExists('tbl_shift_goal_tracking');
    }
}
