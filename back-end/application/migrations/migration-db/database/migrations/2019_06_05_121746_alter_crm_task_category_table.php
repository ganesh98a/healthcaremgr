<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmTaskCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       if (Schema::hasTable('tbl_crm_task_category')) {
            Schema::rename('tbl_crm_task_category', 'tbl_crm_task_priority');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if (Schema::hasTable('tbl_crm_task_priority')) {
          Schema::rename('tbl_crm_task_priority', 'tbl_crm_task_category');
      }
    }
}
