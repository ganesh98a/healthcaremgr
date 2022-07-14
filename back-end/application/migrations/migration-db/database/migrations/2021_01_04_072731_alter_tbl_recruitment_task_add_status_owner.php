<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentTaskAddStatusOwner extends Migration
{
    public function up()
    {
        Schema::table('tbl_recruitment_task', function (Blueprint $table) {         
            if (!Schema::hasColumn('tbl_recruitment_task', 'owner')) {
                $table->unsignedInteger('owner')->nullable()->after('task_name')->comment('reference of tbl_member.id');
                 $table->foreign('owner')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tbl_recruitment_task', 'task_status')) {
                $table->unsignedInteger('task_status')->default(0)->comment('0-Draft/1-Scheduled/2-Open/3-Inprogress/4-Submitted/5-Expired/6-Completed/7-Unsuccessful')->after('owner');
            }          
            if (!Schema::hasColumn('tbl_recruitment_task', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable()->after('created_by')->comment('reference of tbl_member.id');
                 $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_recruitment_task', function (Blueprint $table) {           
            if (Schema::hasColumn('tbl_recruitment_task', 'owner')) {
                $table->dropForeign(['owner']);
                $table->dropColumn('owner');
            }
            if (Schema::hasColumn('tbl_recruitment_task', 'task_status')) {
                $table->dropColumn('task_status');
            }           
            if (Schema::hasColumn('tbl_recruitment_task', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('tbl_recruitment_task', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
}
