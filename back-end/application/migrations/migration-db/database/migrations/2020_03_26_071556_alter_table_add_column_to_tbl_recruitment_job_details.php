<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAddColumnToTblRecruitmentJobDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tbl_recruitment_job_published_detail', function (Blueprint $table) {
         if (!Schema::hasColumn('tbl_recruitment_job_published_detail', 'channel_url')) {
            DB::unprepared("ALTER TABLE `tbl_recruitment_job_published_detail` ADD `channel_url` VARCHAR(200) NULL AFTER `updated`; ");
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
      Schema::table('tbl_recruitment_job_published_detail', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_recruitment_job_published_detail', 'channel_url')) {
              $table->dropColumn('channel_url');
          }
      });
    }
}
