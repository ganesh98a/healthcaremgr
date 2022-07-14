<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentJobPublishedDetailAddColumnChannelUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_job_published_detail')) {
            Schema::table('tbl_recruitment_job_published_detail', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_job_published_detail', 'channel_url')) {
                    $table->string('channel_url',255)->nullable()->comment('URL of job domains');
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
        
       if (Schema::hasTable('tbl_recruitment_job_published_detail')) {
        Schema::table('tbl_recruitment_job_published_detail', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_job_published_detail', 'channel_url')) {
                $table->dropColumn('channel_url');
            }
        });
    }

    }
}
