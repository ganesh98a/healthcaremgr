<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmTaskCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_task_category')) {
            Schema::create('tbl_crm_task_category', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name',50);
                $table->string('code',11);
                $table->unsignedTinyInteger('status')->comment('1-Active,0-InActive');
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
        Schema::dropIfExists('tbl_crm_task_category');
    }
}
