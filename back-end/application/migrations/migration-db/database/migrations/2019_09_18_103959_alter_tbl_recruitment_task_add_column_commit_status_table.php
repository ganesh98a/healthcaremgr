<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentTaskAddColumnCommitStatusTable extends Migration
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
                if(!Schema::hasColumn('tbl_recruitment_task','commit_status')){
                    $table->unsignedSmallInteger('commit_status')->default(0)->comment('0-pending/inprogress,1-completed');
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
        if (Schema::hasTable('tbl_recruitment_task')) {
            Schema::table('tbl_recruitment_task', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_task','commit_status')){
                    $table->dropColumn('commit_status');
                } 
            });

        }
    }
}
