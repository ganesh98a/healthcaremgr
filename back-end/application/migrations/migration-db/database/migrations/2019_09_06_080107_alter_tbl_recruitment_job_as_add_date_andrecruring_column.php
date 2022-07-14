<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobAsAddDateAndrecruringColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_recruitment_job') )
        {
            Schema::table('tbl_recruitment_job', function (Blueprint $table) {
             $table->dateTime('from_date');
             $table->dateTime('to_date');
             $table->unsignedInteger('is_recurring');
             $table->unsignedSmallInteger('job_status')->unsigned()->default(0)->comment('0 = draft, 2 = closed, 3 = live, 4 = Canceled,5 = scheduled')->change();
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
      if (Schema::hasTable('tbl_recruitment_job')) {
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
          $table->dropColumn('from_date');
          $table->dropColumn('to_date');
          $table->dropColumn('is_recurring');
      });
    }
}
}
