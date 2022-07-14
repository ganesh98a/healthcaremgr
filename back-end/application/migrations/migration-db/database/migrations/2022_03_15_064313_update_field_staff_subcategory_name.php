<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFieldStaffSubcategoryName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_category', function (Blueprint $table) {
            DB::table('tbl_recruitment_job_category')
            ->where(['id'=> 3])
            ->update(["name" => 'Residential Youth Workers (CYF)', 
            'description'=> 'Residential Youth Workers (CYF)']);

            DB::table('tbl_recruitment_job_category')
            ->where(['id'=> 4])
            ->update(["name" => 'Disability Support Workers', 
            'description'=> 'Disability Support Workers']);

             DB::table('tbl_recruitment_job_category')
            ->where(['id'=> 5])
            ->update(["name" => 'Oncall Job Ready', 
            'description'=> 'Oncall Job Ready']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_job_category', function (Blueprint $table) {
            //
        });
    }
}
