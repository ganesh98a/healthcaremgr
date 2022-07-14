<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblReferencesAsAddNdisColumnValue extends Migration
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

       Schema::table('tbl_references', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_references', 'deletable')) {
                $table->unsignedInteger('deletable')->after('archive')->comment('1- not deletable, 0- deletable')->default(0);
            }
        });

       $seeder_2 = new ReferenceOpportunityTypeSeeder();
       $seeder_2->run();
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
