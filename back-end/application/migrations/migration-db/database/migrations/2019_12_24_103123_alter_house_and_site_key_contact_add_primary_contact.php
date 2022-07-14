<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterHouseAndSiteKeyContactAddPrimaryContact extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_house_and_site_key_contact', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_house_and_site_key_contact', 'primary_contact')) {
                $table->unsignedInteger('primary_contact')->comment('1 - primary/2 - Secondary')->after("state");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_house_and_site_key_contact', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_house_and_site_key_contact', 'primary_contact')) {
                $table->dropColumn('primary_contact');
            }
        });
    }

}
