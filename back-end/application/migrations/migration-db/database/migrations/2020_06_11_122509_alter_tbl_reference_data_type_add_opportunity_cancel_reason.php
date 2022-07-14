<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblReferenceDataTypeAddOpportunityCancelReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_reference_data_type', function (Blueprint $table) {
            $seeder = new ReferenceDataSeeder();
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
        Schema::table('tbl_reference_data_type', function (Blueprint $table) {
            //
        });
    }
}
