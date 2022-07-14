<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmStageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_stage') && !Schema::hasColumn('tbl_crm_stage','level')) {
        Schema::table('tbl_crm_stage', function (Blueprint $table) {
          $table->unsignedInteger('level');

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
      if(Schema::hasTable('tbl_crm_stage') && Schema::hasColumn('tbl_crm_stage', 'level')) {
        Schema::table('tbl_crm_stage', function (Blueprint $table) {
            $table->dropColumn('level');
        });
      }
    }
}
