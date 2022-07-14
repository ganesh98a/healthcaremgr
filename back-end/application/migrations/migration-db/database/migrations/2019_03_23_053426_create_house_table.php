<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_house')) {
            Schema::create('tbl_house', function(Blueprint $table) {
                $table->increments('id');
                $table->unsignedSmallInteger('companyId')->index('companyId');
                $table->string('name', 64)->index('name');
                $table->string('address', 128);
                $table->string('postal', 10);
                $table->unsignedInteger('state');
                $table->string('created', 20);
                $table->unsignedTinyInteger('status')->comment('1- Active, 0- Inactive');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_house');
    }

}
