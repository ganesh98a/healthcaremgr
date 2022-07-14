<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserPlanLineItemHistoryAddBlockDateUsedDateAndCancelDate extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_user_plan_line_item_history', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_user_plan_line_item_history', 'created')) {
                $table->datetime('created');
            }
            if (!Schema::hasColumn('tbl_user_plan_line_item_history', 'block_action_date')) {
                $table->datetime('block_action_date');
            }
            if (!Schema::hasColumn('tbl_user_plan_line_item_history', 'used_action_date')) {
                $table->datetime('used_action_date');
            }
            if (!Schema::hasColumn('tbl_user_plan_line_item_history', 'relased_action_date')) {
                $table->datetime('relased_action_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_user_plan_line_item_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_user_plan_line_item_history', 'created')) {
                $table->dropColumn('created');
            }
            if (Schema::hasColumn('tbl_user_plan_line_item_history', 'block_action_date')) {
                $table->dropColumn('block_action_date');
            }
            if (Schema::hasColumn('tbl_user_plan_line_item_history', 'used_action_date')) {
                $table->dropColumn('used_action_date');
            }
            if (Schema::hasColumn('tbl_user_plan_line_item_history', 'relased_action_date')) {
                $table->dropColumn('relased_action_date');
            }
        });
    }

}
