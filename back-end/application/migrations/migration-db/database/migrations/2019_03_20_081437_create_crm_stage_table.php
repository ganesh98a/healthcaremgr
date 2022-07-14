<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmStageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_stage')) {
            Schema::create('tbl_crm_stage', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name',50);
                $table->unsignedInteger('parent_id')->comment('0-for parent');
                $table->unsignedTinyInteger('status')->comment('1- Active, 0- Inactive');
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
        Schema::dropIfExists('tbl_crm_stage');
    }
}
