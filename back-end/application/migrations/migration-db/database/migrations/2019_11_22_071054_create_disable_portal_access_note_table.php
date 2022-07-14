<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDisablePortalAccessNoteTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_disable_portal_access_note')) {
            Schema::create('tbl_disable_portal_access_note', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('userId')->comment('primary key tbl_member/tbl_participant/tbl_organisation');

                $table->unsignedInteger('user_type')->comment('1 - member/2 - Participant/3 - organziation');
                $table->text('note');
                $table->unsignedInteger('action_by')->comment('disabled by admin (tbl_member primary key)');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_disable_portal_access_note');
    }

}
