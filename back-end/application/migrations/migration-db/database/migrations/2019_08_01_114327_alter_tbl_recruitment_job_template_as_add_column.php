<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobTemplateAsAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_job_template')) {
            Schema::table('tbl_recruitment_job_template', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_job_template','name')) {
                  $table->string('name',200);
              }

              if (!Schema::hasColumn('tbl_recruitment_job_template','thumb')) {
                  $table->string('thumb',100);
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
        if (Schema::hasTable('tbl_recruitment_job_template')) {
            Schema::table('tbl_recruitment_job_template', function (Blueprint $table) {
              $table->dropColumn('name');
              $table->dropColumn('thumb');
          });
        }
    }
}
