<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblParticipantAddNewStatusAsDraftInCommentInColumnStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participant', function (Blueprint $table) {
           if (Schema::hasColumn('tbl_participant', 'status')) {
                $table->unsignedSmallInteger('status')->default(0)->comment('1- Active, 0- Inactive, 2 - Draft')->change();
            }
        });
		Schema::table('tbl_user_active_inactive_history', function (Blueprint $table) {
           if (Schema::hasColumn('tbl_user_active_inactive_history', 'action_type')) {
                $table->unsignedSmallInteger('action_type')->default(1)->comment('	1-enable/2 - disabled/ 3 - Draft')->change();
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
        Schema::table('tbl_participant', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participant', 'status')) {
                $table->unsignedSmallInteger('status')->default(0)->comment('1- Active, 0- Inactive')->change();
            }
        });
		Schema::table('tbl_user_active_inactive_history', function (Blueprint $table) {
           if (Schema::hasColumn('tbl_user_active_inactive_history', 'action_type')) {
                $table->unsignedSmallInteger('action_type')->default(1)->comment('	1-enable/2 - disabled')->change();
            }
        });
    }
}
