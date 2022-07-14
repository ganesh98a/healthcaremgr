<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameParticipantPlanChangeParticipantToUser extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_user_plan') && Schema::hasTable('tbl_participant_plan')) {
            Schema::table('tbl_participant_plan', function (Blueprint $table) {
                Schema::rename('tbl_participant_plan', 'tbl_user_plan');
            });

            Schema::table('tbl_user_plan', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_user_plan', 'user_type')) {
                    $table->unsignedInteger('user_type')->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote')->after('id');
                }
                if (!Schema::hasColumn('tbl_user_plan', 'userId')) {
                    $table->unsignedInteger('userId')->after('user_type');
                }
                if (Schema::hasColumn('tbl_user_plan', 'participantId')) {
                    $table->dropColumn('participantId');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (!Schema::hasTable('tbl_participant_plan') && Schema::hasTable('tbl_user_plan')) {
            Schema::table('tbl_user_plan', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_user_plan', 'user_type')) {
                    $table->dropColumn('user_type');
                }
                if (Schema::hasColumn('tbl_user_plan', 'userId')) {
                    $table->dropColumn('userId');
                }
            });

            Schema::table('tbl_participant_plan', function (Blueprint $table) {
                Schema::rename('tbl_user_plan', 'tbl_participant_plan');
            });
        }
    }

}
