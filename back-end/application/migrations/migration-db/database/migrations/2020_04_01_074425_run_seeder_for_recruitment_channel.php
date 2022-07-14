<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunSeederForRecruitmentChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_channel', function (Blueprint $table) {
            $seeder = new RecruitmentChannelSeeder();
            $seeder->run();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_channel', function (Blueprint $table) {
            //
        });
    }
}
