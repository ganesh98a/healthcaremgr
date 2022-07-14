<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateIsSysGeneraterInRecruitmentForm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::table('tbl_recruitment_form_applicant')
            ->where(['status' => 2, 'start_datetime' => NULL, 'end_datetime' => NULL])
            ->update(["is_sys_generater" => 1]);
           
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
