<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunSeederForTblReferenceTypeAddRefenceTypeTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Re-run reference data type seeder to add 'title' reference type
        // tbl_reference_data_type is neither insert-able nor update-able by the user
        // so this seeder is safe to re-run
        $seeder = new ReferenceDataSeeder(); // <-- misleading classname?
        $seeder->run();
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
