<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftMemberAddArchiveColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_member', 'archive')) {
                $table->unsignedSmallInteger('archive')->comment('0 - Not/1 - Yes');
            }
            if (!Schema::hasColumn('tbl_shift_member', 'updated')) {
                $table->dateTime('updated');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_member', 'archive')) {
                $table->dropColumn('archive');
            }
            if (Schema::hasColumn('tbl_shift_member', 'updated')) {
                $table->dropColumn('updated');
            }
        });
    }

}
