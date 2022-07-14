<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentTaskUpdateRelevantTaskNoteAllowToNullableValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_recruitment_task')){
            Schema::table('tbl_recruitment_task', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_task','relevant_task_note')){
                    $table->text('relevant_task_note')->nullable()->default('')->change();
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
        if(Schema::hasTable('tbl_recruitment_task')){
            Schema::table('tbl_recruitment_task', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_task','relevant_task_note')){
                    $table->text('relevant_task_note')->change();
                }
            });
        }
    }
}
