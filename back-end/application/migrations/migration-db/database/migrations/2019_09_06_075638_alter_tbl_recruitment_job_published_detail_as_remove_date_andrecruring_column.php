<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobPublishedDetailAsRemoveDateAndrecruringColumn extends Migration
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
          $table->dropColumn('from_date');
          $table->dropColumn('to_date');
          $table->dropColumn('is_recurring');
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
        if(Schema::hasTable('tbl_recruitment_job_published_detail') )
        {
            Schema::table('tbl_recruitment_job_published_detail', function (Blueprint $table) {
             $table->dateTime('from_date');
             $table->dateTime('to_date');
             $table->unsignedInteger('is_recurring');
         });
        }
    }
}
