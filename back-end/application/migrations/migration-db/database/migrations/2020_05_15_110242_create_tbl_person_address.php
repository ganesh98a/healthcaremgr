<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPersonAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
		if (!Schema::hasTable('tbl_person_address')) {
            Schema::create('tbl_person_address', function(Blueprint $table)
                {
                    $table->bigIncrements('id');
					
					$table->bigInteger('person_id')->unsigned()->comment("tbl_person.id");
					$table->foreign('person_id')->references('id')->on('tbl_person'); 
					
                    $table->unsignedTinyInteger('primary_address')->comment('1- Primary, 2- Secondary');
                    $table->string('street', 200);
                    $table->string('suburb', 100);
                    $table->unsignedInteger('postcode');
					
					$table->integer('state')->unsigned()->comment("tbl_state.id");
					$table->foreign('state')->references('id')->on('tbl_state'); 
					
                    $table->string('lat', 200)->nullable();
                    $table->string('long', 200)->nullable();
					
					$table->dateTime('created');
                    $table->unsignedTinyInteger('archive')->default(0)->comment('0 - Not/ 1 - Yes');
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
        Schema::dropIfExists('tbl_person_address');
    }
}
