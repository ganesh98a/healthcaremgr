<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentActionNotificationAddActionAndUpdatedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_action_notification')) {
            Schema::table('tbl_recruitment_action_notification', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_action_notification','updated')) {
                    $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                }

                if (!Schema::hasColumn('tbl_recruitment_action_notification','status')) {
                    $table->unsignedSmallInteger('status')->default('0')->comment('0-not yet, 1-view, 2-dismiss');
                }

                if (!Schema::hasColumn('tbl_recruitment_action_notification','action_at')) {
                    $table->dateTime('action_at')->default('0000-00-00 00:00:00')->comment('when notification is marked as dismiss or view current date time save on this field');
                }
                if (!Schema::hasColumn('tbl_recruitment_action_notification','action_by')) {
                    $table->unsignedInteger('action_by')->default('0')->comment('when notification is marked as dismiss or view which recuiter admin perform this action');
                }
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
        if (Schema::hasTable('tbl_recruitment_action_notification')) {
            Schema::table('tbl_recruitment_action_notification', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_action_notification','updated')) {
                    $table->dropColumn('updated');
                }

                if (Schema::hasColumn('tbl_recruitment_action_notification','action_at')) {
                    $table->dropColumn('action_at');
                }

                if (Schema::hasColumn('tbl_recruitment_action_notification','status')) {
                    $table->dropColumn('status');
                }

                if (Schema::hasColumn('tbl_recruitment_action_notification','action_by')) {
                    $table->dropColumn('action_by');
                }
            });
        }
    }
}
