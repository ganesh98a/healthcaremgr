<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AlterRunRecruitmentStageNStageLebelSeederForAddNewStageOffer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $seeder = new RecruitmentStage();
        $seeder->run();

        #update on other file where column is also created for same table
        /*$seeder_2 = new RecruitmentStageLabel();
        $seeder_2->run();*/
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
