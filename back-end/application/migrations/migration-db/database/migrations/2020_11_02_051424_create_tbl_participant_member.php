<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblParticipantMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_member')) {
            Schema::create('tbl_participant_member', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participant_id')->comment('tbl_participant.id');
                $table->foreign('participant_id')->references('id')->on('tbl_participants_master')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('member_id')->comment('tbl_member.id');
                $table->foreign('member_id')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('status')->nullable()->comment('tbl_references.id');
                $table->foreign('status')->references('id')->on('tbl_references')->onDelete(DB::raw('SET NULL'));
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
        Schema::table('tbl_participant_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participant_member', 'participant_id')) {
                $table->dropForeign(['participant_id']);
            }
            if (Schema::hasColumn('tbl_participant_member', 'member_id')) {
                $table->dropForeign(['member_id']);
            }
            if (Schema::hasColumn('tbl_participant_member', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_participant_member', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
            if (Schema::hasColumn('tbl_participant_member', 'status')) {
                $table->dropForeign(['status']);
            }
        });
        Schema::dropIfExists('tbl_participant_member');
    }
}
