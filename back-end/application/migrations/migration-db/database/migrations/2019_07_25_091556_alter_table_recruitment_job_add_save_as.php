<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentJobAddSaveAs extends Migration
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
               if (!Schema::hasColumn('tbl_recruitment_job','save_as')) {
                  $table->unsignedInteger('save_as')->comment('0 = save as draft,1 = posted on selected channel')->nullable();
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
      if(Schema::hasTable('tbl_recruitment_job') && Schema::hasColumn('tbl_recruitment_job', 'save_as') ) {
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
            $table->dropColumn('save_as');
        });
    }
}
}
