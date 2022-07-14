<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentOaTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_oa_template')) {
            Schema::create('tbl_recruitment_oa_template', function (Blueprint $table) {
                $table->increments('id');                
                $table->longText('title')->nullable();  
                $table->unsignedInteger('job_type')->comment('job type 3:CYF, 4:Disability,5:Job Ready');
                $table->string('location',5)->nullable()->comment('location NA,QLD,VIC');
                $table->smallInteger('archive')->default(0); 
                $table->smallInteger('status')->comment('status 1:Active,2:Inactive');            
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('job_type','oa_template_job_type')->references('id')->on('tbl_recruitment_job_category');
                $table->foreign('created_by','oa_template_created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by','oa_template_updated_by')->references('id')->on('tbl_users'); 
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
        if (Schema::hasTable('tbl_recruitment_oa_template')) {
            Schema::dropIfExists('tbl_recruitment_oa_template');
        }
    }
}
