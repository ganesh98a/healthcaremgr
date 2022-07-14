<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblModuleTvSlide extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_module_tv_slide')) {
            Schema::create('tbl_module_tv_slide', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('memberId')->default(0)->comment('primary key tbl_member');
                $table->unsignedInteger('moduleId')->default(0)->comment('primary key tbl_module_title');
                $table->unsignedInteger('module_graphId')->default(0)->comment('primary key tbl_module_graph');
                $table->unsignedInteger('slide_position')->default(0);
                $table->dateTime('created');
                $table->unsignedSmallInteger('archive')->default(0)->comment('0 - No/1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_module_tv_slide');
    }

}
