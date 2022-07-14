<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberVisaType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_visa_type')) {
            Schema::create('tbl_member_visa_type', function (Blueprint $table) {
                $table->increments('id');                
                $table->string('visa_type_no')->nullable();  
                $table->string('visa_type');
                $table->unsignedInteger('visa_type_category_id')->default(0)->comment('tbl_member_visa_type_category.id');
                $table->smallInteger('recheck_every_three_months')->default(0)->comment("0 - false/1 - true");
                $table->smallInteger('recheck_every_six_months')->default(0)->comment("0 - false/1 - true");
                $table->smallInteger('not_eligible_for_work')->default(0)->comment('0 - false/1 - true');
                $table->smallInteger('permanent_status')->nullable()->comment('0 - false/1 - true');
                $table->string('visa_class')->nullable();
                $table->string('hours_per_week_restriction')->nullable();
                $table->smallInteger('archive')->default(0)->comment("0 - Not/1 - Yes"); 
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by','visa_type_created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by','visa_type_updated_by')->references('id')->on('tbl_users'); 
                $table->foreign('visa_type_category_id','member_visa_type_category_id')->references('id')->on('tbl_member_visa_type_category');
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
        if (Schema::hasTable('tbl_member_visa_type')) {
            Schema::dropIfExists('tbl_member_visa_type');
        }
    }
}
