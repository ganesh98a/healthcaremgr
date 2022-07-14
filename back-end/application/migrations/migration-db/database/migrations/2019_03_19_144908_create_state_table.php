<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_state')) {
            Schema::create('tbl_state', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('country_id')->nullable();
                $table->string('name',100);
                $table->unsignedTinyInteger('archive')->comment('1- Delete');
                $table->timestamp('created')->default('0000-00-00 00:00:00');                
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
        Schema::dropIfExists('tbl_state');
    }
}
