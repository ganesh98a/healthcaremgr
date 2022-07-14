<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftAddRosterShiftId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'roster_shiftId')) {
                $table->unsignedInteger('roster_shiftId')->default(0)->nullable()->comment("primary key of tbl_roster_shift")->after("id");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'roster_shiftId')) {
                $table->dropColumn('roster_shiftId');
            }
        });
    }

}
