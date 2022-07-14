<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_vendor')) {
          Schema::create('tbl_vendor', function (Blueprint $table) {
              $table->increments('id');
              $table->text('name');
              $table->unsignedInteger('crm_participant_id');
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
        Schema::dropIfExists('tbl_vendor');
    }
}
