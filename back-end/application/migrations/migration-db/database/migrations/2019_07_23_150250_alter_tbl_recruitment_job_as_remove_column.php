<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobAsRemoveColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_recruitment_job')) {
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
          $table->dropColumn('location');
          $table->dropColumn('requirement_docs');
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
      if(Schema::hasTable('tbl_recruitment_job') && Schema::hasColumn('tbl_recruitment_job', 'status') ) {
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
        $table->unsignedInteger('location');
        $table->unsignedInteger('requirement_docs');
        });
      }
    }
}
