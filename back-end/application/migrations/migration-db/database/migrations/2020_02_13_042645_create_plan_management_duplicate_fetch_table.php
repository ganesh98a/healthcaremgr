<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanManagementDuplicateFetchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_plan_management_duplicate_fetch')) {
        Schema::create('tbl_plan_management_duplicate_fetch', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('last_updated_date')->useCurrent();
            $table->unsignedInteger('last_fetch_id');
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
        Schema::dropIfExists('tbl_plan_management_duplicate_fetch');
    }
}
