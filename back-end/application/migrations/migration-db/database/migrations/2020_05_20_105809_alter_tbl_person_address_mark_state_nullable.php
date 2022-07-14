<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddressMarkStateNullable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_person_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person_address', 'state')) {
                $table->integer('state')->unsigned()->comment("tbl_state.id")->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_person_address', function (Blueprint $table) {
            //
        });
    }

}
