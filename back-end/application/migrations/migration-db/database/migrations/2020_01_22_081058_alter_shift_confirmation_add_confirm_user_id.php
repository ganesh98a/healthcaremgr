<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftConfirmationAddConfirmUserId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift_confirmation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_confirmation', 'confirm_userId')) {
                $table->unsignedInteger('confirm_userId')->comment('primary key of booker/key contact and booking contact')->after("confirm_with");
            }
            if (Schema::hasColumn('tbl_shift_confirmation', 'confirmed_with_allocated')) {
                $table->dropColumn('confirmed_with_allocated');
            }
            if (Schema::hasColumn('tbl_shift_confirmation', 'confirmed_on')) {
                $table->dropColumn('confirmed_on');
            }
        });

        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_member', 'confirmed_with_allocated')) {
                $table->dateTime('confirmed_with_allocated')->after("confirm_on");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_shift_confirmation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_confirmation', 'confirm_userId')) {
                $table->dropColumn('confirm_userId')->comment('primary key of tbl_participant_genral and type assistance');
            }
        });

        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_member', 'confirmed_with_allocated')) {
                $table->dropColumn('confirmed_with_allocated');
            }
        });
    }

}
