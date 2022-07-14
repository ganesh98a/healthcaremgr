<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddressAddAddressType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_person_address', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person_address', 'address_type')) {
                $table->unsignedSmallInteger('address_type')->comment("1 = billing, 2 = shipping")->after("primary_address");
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
            if (Schema::hasColumn('tbl_person_address', 'address_type')) {
                $table->dropColumn('address_type');
            }
        });
    }

}
