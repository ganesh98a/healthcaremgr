<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftMemberAddPrimaryKey extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
            DB::unprepared('ALTER TABLE `tbl_shift_member` DROP INDEX IF EXISTS `PRIMARY`;');
            if (Schema::hasColumn('tbl_shift_member', 'shiftId')) {
                $table->unsignedInteger('shiftId')->comment('priamry key tbl_shift')->change();
            }
            if (!Schema::hasColumn('tbl_shift_member', 'id')) {
                $table->increments('id')->first();
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
            if (Schema::hasColumn('tbl_shift_member', 'id')) {
                $table->dropColumn('id');
            }
        });
    }

}
