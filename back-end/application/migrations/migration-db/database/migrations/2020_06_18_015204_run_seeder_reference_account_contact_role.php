<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunSeederReferenceAccountContactRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fortunately, this seeder is safe to re-run
        $seeder = new ReferenceDataSeeder();
        $seeder->run();

        // Planning to run this seeder again? If reference data are editable and user 
        // has made changes and you run this seeder again, you'll risk creating duplicate records.
        // ATM (18 june 2020) there was no feat to edit reference data
        $seeder2 = new ReferencesAccountContactRole();
        $seeder2->run();
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
