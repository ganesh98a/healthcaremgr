<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblOpportunityType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_opportunity_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('key_name', 255);
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });
        
        $seeder = new OpportunityTypeSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_opportunity_type');
    }
}
