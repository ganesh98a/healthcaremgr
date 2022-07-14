<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterParticipantRosterRenameTableAndAddColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_participant_roster', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_participant_roster', 'booked_by')) {
                $table->unsignedInteger('booked_by')->comment("1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote/7-house")->after("id");
            }

            if (!Schema::hasColumn('tbl_participant_roster', 'userId') && Schema::hasColumn('tbl_participant_roster', 'participantId')) {
                $table->renameColumn('participantId', 'userId');
            }

            if (Schema::hasColumn('tbl_participant_roster', 'is_default')) {
                $table->unsignedInteger('is_default')->comment("1 - other/2 - default")->change();
            }

            if (Schema::hasColumn('tbl_participant_roster', 'start_date')) {
                $table->date('start_date')->change();
            }

            if (Schema::hasColumn('tbl_participant_roster', 'end_date')) {
                $table->date('end_date')->change();
            }


            if (!Schema::hasColumn('tbl_participant_roster', 'created_type')) {
                $table->unsignedInteger('created_type')->comment("1 - admin/2 - user")->after("status");
            }

            if (!Schema::hasColumn('tbl_participant_roster', 'created_by')) {
                $table->unsignedInteger('created_by')->comment("primary key of tbl_member/primary key of user like tbl_participant")->after("created_type");
            }
        });

        Schema::table('tbl_participant_roster', function (Blueprint $table) {
            if (Schema::hasTable('tbl_participant_roster') && !Schema::hasTable('tbl_roster')) {
                Schema::rename("tbl_participant_roster", "tbl_roster");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_roster', function (Blueprint $table) {
            if (!Schema::hasTable('tbl_participant_roster') && Schema::hasTable('tbl_roster')) {
                Schema::rename("tbl_roster", "tbl_participant_roster");
            }
        });

        Schema::table('tbl_participant_roster', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participant_roster', 'booked_by')) {
                $table->dropColumn('booked_by')->comment("1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote/7-house");
            }
            if (Schema::hasColumn('tbl_participant_roster', 'userId')) {
                $table->renameColumn('userId', 'participantId');
            }

            if (Schema::hasColumn('tbl_participant_roster', 'archive')) {
                $table->dropColumn('archive');
            }

            if (Schema::hasColumn('tbl_participant_roster', 'created_type')) {
                $table->dropColumn('created_type');
            }

            if (Schema::hasColumn('tbl_participant_roster', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }

}
