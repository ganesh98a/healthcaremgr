<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'accepted_shift_member_id')) {
                $table->unsignedInteger('accepted_shift_member_id')->nullable()->comment('tbl_shift_member.id')->after('account_id');
                $table->foreign('accepted_shift_member_id')->references('id')->on('tbl_shift_member')->onDelete(DB::raw('SET NULL'));
            }
        });

        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_member', 'status')) {
                $table->unsignedInteger('status')->default('0')->comment('0 = pending, 1 = accepted, 2 = rejected');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'accepted_shift_member_id')) {
                $table->dropForeign(['accepted_shift_member_id']);
                $table->dropColumn('accepted_shift_member_id');
            }
        });

        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_member', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
