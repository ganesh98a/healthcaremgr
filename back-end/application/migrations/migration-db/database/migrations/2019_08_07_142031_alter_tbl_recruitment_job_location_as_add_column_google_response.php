<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobLocationAsAddColumnGoogleResponse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_job_location')) {
            Schema::table('tbl_recruitment_job_location', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_job_location','google_response')) {
                  $table->text('google_response')->nullable();
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
        if (Schema::hasTable('tbl_recruitment_job_location')) {
            Schema::table('tbl_recruitment_job_location', function (Blueprint $table) {
              $table->dropColumn('google_response');
          });
        }
    }
}
