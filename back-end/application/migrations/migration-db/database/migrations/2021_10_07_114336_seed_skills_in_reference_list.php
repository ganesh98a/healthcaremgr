<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedSkillsInReferenceList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dbseeder = new SkillSetListFromOcpSeeder();
        $dbseeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $dbseeder = new SkillSetListFromOcpSeeder();
        $dbseeder->rollback();
        //
    }
}
