<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReligiousBeliefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        		
		if (!Schema::hasTable('tbl_religious_beliefs')) {
            Schema::create('tbl_religious_beliefs', function(Blueprint $table)
                {
                    $table->increments('id');                    
                    $table->string('name', 200);
                    $table->unsignedTinyInteger('order');
					$table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));					
                    $table->unsignedTinyInteger('archive')->comment('0- not /1 - archive');
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
        Schema::dropIfExists('tbl_religious_beliefs');
    }
}
