<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberVisaTypeCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_visa_type_category')) {
            Schema::create('tbl_member_visa_type_category', function (Blueprint $table) {
                $table->increments('id');                
                $table->string('category')->nullable()->comment('Visa category');  
                $table->smallInteger('archive')->default(0)->comment("0 - Not/1 - Yes");                      
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by','visa_category_created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by','visa_category_updated_by')->references('id')->on('tbl_users'); 
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
        if (Schema::hasTable('tbl_member_visa_type_category')) {
            Schema::dropIfExists('tbl_member_visa_type_category');
        }
    }
}
