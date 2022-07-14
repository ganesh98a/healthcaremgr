<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActiveInactiveHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('tbl_user_active_inactive_history')) {
            Schema::create('tbl_user_active_inactive_history', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('userId')->comment('primary key tbl_member/tbl_participant/tbl_organisation');
                $table->unsignedInteger('user_type')->comment('1 - member/2 - Participant/3 - organziation');
                $table->smallInteger('action_type')->comment('1-enable/2 - disabled');
                $table->unsignedInteger('action_by')->comment('disabled by admin (tbl_member primary key)');
				$table->dateTime('created')->default('0000-00-00 00:00:00');
				 $table->smallInteger('archive')->comment('0 -Not/1 - Archive');
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
        Schema::dropIfExists('tbl_user_active_inactive_history');
    }
}
