<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentTaskAddUpdatedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_task')) {
            Schema::table('tbl_recruitment_task', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_task','updated')) {
                    $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                }

                if (!Schema::hasColumn('tbl_recruitment_task','action_at')) {
                    $table->dateTime('action_at')->default('0000-00-00 00:00:00')->comment('when task is marked as completed or archive current date time save on this field');
                 }
          });
          if (Schema::hasColumn('tbl_recruitment_task','action_at')) {
            DB::unprepared("UPDATE tbl_recruitment_task set action_at=now() where status in ('2','4')");
          }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_recruitment_task')) {
            Schema::table('tbl_recruitment_task', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_task','updated')) {
                    $table->dropColumn('updated');
                }

                if (Schema::hasColumn('tbl_recruitment_task','action_at')) {
                    $table->dropColumn('action_at');
                }
          });
        }
    }
}
