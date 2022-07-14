<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentTaskAsRemoveComment extends Migration
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
              $table->unsignedInteger('task_stage')->comment('')->change();
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
              $table->unsignedInteger('task_stage')->comment('0=group interview ,1= single')->change();
        });
      }
    }
}
