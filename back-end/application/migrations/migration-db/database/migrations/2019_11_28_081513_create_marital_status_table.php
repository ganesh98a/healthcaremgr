<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaritalStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){	
		if (!Schema::hasTable('tbl_marital_status')) {
            Schema::create('tbl_marital_status', function(Blueprint $table)
                {
                    $table->increments('id');                    
                    $table->string('name', 200);
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
        Schema::dropIfExists('tbl_marital_status');
    }
}
