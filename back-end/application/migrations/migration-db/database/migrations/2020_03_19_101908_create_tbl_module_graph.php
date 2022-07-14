<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblModuleGraph extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_module_graph', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->unsignedInteger('moduleId')->default(0)->comment('primary key tbl_module_title');
            $table->string('key_name', 200);
            $table->unsignedSmallInteger('graph_type')->default(1)->comment('1-marketing/2 - hcm');
            $table->unsignedInteger('order');
            $table->unsignedSmallInteger('archive')->default(0)->comment('0 - No/1 - Yes');
        });


        $seeder = new ModuleGraph();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_module_graph');
    }

}
