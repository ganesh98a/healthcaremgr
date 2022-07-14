<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTblContactTypeToTblPersonType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_contact_type', function (Blueprint $table) {
			if (Schema::hasTable('tbl_contact_type') && !Schema::hasTable('tbl_person_type')) {
				Schema::rename("tbl_contact_type", "tbl_person_type");
			}

            $seeder = new PersonTypeSeeder();
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
        Schema::table('tbl_contact_type', function (Blueprint $table) {
            if (Schema::hasTable('tbl_person_type') && !Schema::hasTable('tbl_contact_type')) {
				Schema::rename("tbl_person_type", "tbl_contact_type");
			}
        });
    }
}
