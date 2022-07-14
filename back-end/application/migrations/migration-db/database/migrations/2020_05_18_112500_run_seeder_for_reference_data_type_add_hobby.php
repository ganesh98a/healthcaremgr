<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunSeederForReferenceDataTypeAddHobby extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $seeder_1 = new ReferenceDataSeeder();
        $seeder_1->run();

        $seeder = new ReferencesHobbies();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_reference_data_type', function (Blueprint $table) {
            //
        });
    }
}
