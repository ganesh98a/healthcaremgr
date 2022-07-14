<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassificationLevelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_classification_level')) {
            Schema::create('tbl_classification_level', function (Blueprint $table) {
                $table->increments('id');
                $table->string('level_name',100);
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedInteger('level_priority');
                $table->dateTime('created');
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
        Schema::dropIfExists('tbl_classification_level');
    }
}
