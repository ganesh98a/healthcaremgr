<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblReferenceDataTypeNRunSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_reference_data_type')) {
            Schema::create('tbl_reference_data_type', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 200);
                $table->string('key_name', 200);
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('archive')->comment('1- delete');
            });
        }
        
        if (Schema::hasTable('tbl_reference_data_type')) {
            $seeder = new ReferenceDataSeeder();
            $seeder->run();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_reference_data_type');
    }
}
