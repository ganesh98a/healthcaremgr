<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsUrlDesignTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_ms_url_template')) {
            Schema::create('tbl_ms_url_template', function (Blueprint $table) {
                $table->increments('id');                
                $table->longText('template')->nullable();  
                $table->string('type')->nullable();
                $table->smallInteger('archive')->default(0);              
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by')->references('id')->on('tbl_users'); 
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
        if (Schema::hasTable('tbl_ms_url_template')) {
            Schema::dropIfExists('tbl_ms_url_template');
        }
    }
}
