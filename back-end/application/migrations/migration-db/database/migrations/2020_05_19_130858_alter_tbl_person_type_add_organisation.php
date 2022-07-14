<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonTypeAddOrganisation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_person_type', function (Blueprint $table) {
            $seeder = new PersonTypeSeeder();
            $seeder->run();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_person_type', function (Blueprint $table) {
            //
        });
    }

}
