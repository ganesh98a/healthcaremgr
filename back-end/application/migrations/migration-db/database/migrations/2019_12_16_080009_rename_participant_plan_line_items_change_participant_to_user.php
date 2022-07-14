<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameParticipantPlanLineItemsChangeParticipantToUser extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_user_plan_line_items') && Schema::hasTable('tbl_participant_plan_line_items')) {
            Schema::table('tbl_participant_plan_line_items', function (Blueprint $table) {
                Schema::rename('tbl_participant_plan_line_items', 'tbl_user_plan_line_items');
            });
        }

        Schema::table('tbl_user_plan_line_items', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_user_plan_line_items', 'participantId')) {
                $table->dropColumn('participantId');
            }
            if (Schema::hasColumn('tbl_user_plan_line_items', 'participant_planId')) {
                $table->dropColumn('participant_planId');
            }
            
            if (!Schema::hasColumn('tbl_user_plan_line_items', 'user_planId')) {
                $table->unsignedInteger('user_planId')->commnet('priamry key tbl_user_plan')->after('line_itemId');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (!Schema::hasTable('tbl_user_plan_line_items') && Schema::hasTable('tbl_participant_plan_line_items')) {
            Schema::table('tbl_participant_plan_line_items', function (Blueprint $table) {
                Schema::rename('tbl_user_plan_line_items', 'tbl_participant_plan_line_items');
            });
        }
    }

}
