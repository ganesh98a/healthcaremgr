<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblReferenceForOppContactRolesAndRunSeederDatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
        $seeder = new ReferenceDataSeeder();
        $seeder->run();
        
        $seeder_1 = new ReferencesOpportunityContactRole();
        $seeder_1->run();
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_references', function (Blueprint $table) {
            //
        });
    }
}
