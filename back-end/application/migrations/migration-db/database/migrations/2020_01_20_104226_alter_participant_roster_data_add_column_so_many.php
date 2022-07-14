<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterParticipantRosterDataAddColumnSoMany extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_participant_roster_data', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_participant_roster_data', 'preferred_memberId')) {
                $table->unsignedInteger('preferred_memberId')->comment("primary key tbl_member");
            }
            if (!Schema::hasColumn('tbl_participant_roster_data', 'allocate_pre_member')) {
                $table->unsignedInteger('allocate_pre_member')->comment("1- Yes, 0- No");
            }
            if (!Schema::hasColumn('tbl_participant_roster_data', 'autofill_shift')) {
                $table->unsignedInteger('autofill_shift')->comment("1- Yes, 0- No");
            }
            if (!Schema::hasColumn('tbl_participant_roster_data', 'push_to_app')) {
                $table->unsignedInteger('push_to_app')->comment("1- Yes/ 0- No/ 2 reserve");
            }
            if (!Schema::hasColumn('tbl_participant_roster_data', 'shift_note')) {
                $table->text('shift_note');
            }

            if (!Schema::hasColumn('tbl_participant_roster_data', 'sub_total')) {
                $table->double('sub_total', 14, 2);
            }

            if (!Schema::hasColumn('tbl_participant_roster_data', 'created')) {
                $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            }

            if (!Schema::hasColumn('tbl_participant_roster_data', 'updated')) {
                $table->dateTime('updated');
            }

            if (!Schema::hasColumn('tbl_participant_roster_data', 'archive')) {
                $table->unsignedInteger('archive');
            }
        });

        Schema::table('tbl_participant_roster_data', function (Blueprint $table) {
            if (Schema::hasTable('tbl_participant_roster_data') && !Schema::hasTable('tbl_roster_shift')) {
                Schema::rename("tbl_participant_roster_data", "tbl_roster_shift");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_participant_roster_data', function (Blueprint $table) {
            if (!Schema::hasTable('tbl_participant_roster_data') && Schema::hasTable('tbl_roster_shift')) {
                Schema::rename("tbl_roster_shift", "tbl_participant_roster_data");
            }
        });

        Schema::table('tbl_participant_roster_data', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participant_roster_data', 'allocate_pre_member')) {
                $table->dropColumn('allocate_pre_member')->comment("1- Yes, 0- No");
            }
            if (Schema::hasColumn('tbl_participant_roster_data', 'autofill_shift')) {
                $table->dropColumn('autofill_shift')->comment("1- Yes, 0- No");
            }
            if (Schema::hasColumn('tbl_participant_roster_data', 'push_to_app')) {
                $table->dropColumn('push_to_app')->comment("1- Yes/ 0- No/ 2 reserve");
            }
            if (Schema::hasColumn('tbl_participant_roster_data', 'shift_note')) {
                $table->dropColumn('shift_note');
            }

            if (Schema::hasColumn('tbl_participant_roster_data', 'created')) {
                $table->dropColumn('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            }
            if (Schema::hasColumn('tbl_participant_roster_data', 'updated')) {
                $table->dropColumn('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
            }

            if (Schema::hasColumn('tbl_participant_roster_data', 'archive')) {
                $table->dropColumn('archive');
            }

            if (Schema::hasColumn('tbl_participant_roster_data', 'sub_total')) {
                $table->dropColumn('sub_total');
            }
        });
    }

}
