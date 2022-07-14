<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblInfographicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_infographics', function (Blueprint $table) {
			$table->increments('id');
			$table->text('page_module');
			$table->text('page_url');
			$table->text('block_title');
			$table->text('block_desc');
			$table->text('block_image');
			$table->unsignedTinyInteger('status')->default(1)->comment('0- inactive, 1- active'); 
			$table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->dateTime('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_infographics');
    }
}
