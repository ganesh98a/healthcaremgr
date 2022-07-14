<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobLocationAsAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {if (Schema::hasTable('tbl_recruitment_job_location')) {
        Schema::table('tbl_recruitment_job_location', function (Blueprint $table) {
          $table->string('complete_address',200)->comment('complete address from google')->nullable();
        });
      }}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if(Schema::hasTable('tbl_recruitment_job_location') && Schema::hasColumn('tbl_recruitment_job_location', 'status') ) {
        Schema::table('tbl_recruitment_job_location', function (Blueprint $table) {
        $table->dropColumn('complete_address');
        });
      }
    }
}
