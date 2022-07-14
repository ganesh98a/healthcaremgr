<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # rename the existing table to preserve it
        if (Schema::hasTable('tbl_shift_member')) {
            Schema::dropIfExists('tbl_shift_member_old');
            DB::statement("ALTER TABLE tbl_shift_member RENAME TO tbl_shift_member_old");
        }

        if (!Schema::hasTable('tbl_shift_member')) {
            Schema::create('tbl_shift_member', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shift_id')->comment('tbl_shift.id');
                $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('member_id')->comment('tbl_member.id');
                $table->foreign('member_id')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->dateTime('created')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->dateTime('updated')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_member', 'shift_id')) {
                $table->dropForeign(['shift_id']);
            }
            if (Schema::hasColumn('tbl_shift_member', 'member_id')) {
                $table->dropForeign(['member_id']);
            }
            if (Schema::hasColumn('tbl_shift_member', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_shift_member', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_shift_member');
    }
}
