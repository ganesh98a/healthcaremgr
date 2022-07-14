<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubModuleTitle extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_sub_module_title')) {
            Schema::create('tbl_sub_module_title', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('key_name', 255);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->unsignedInteger('archive')->comment('0 -NO/1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_sub_module_title');
    }

}
