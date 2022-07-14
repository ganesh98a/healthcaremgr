<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterKeypayAuthDetailsAddLocationIdColomn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_keypay_auth_details', function (Blueprint $table) {
            Schema::table('tbl_keypay_auth_details', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_keypay_auth_details', 'location_id')) {
                    $table->string('location_id', 255)->nullable()->comment('keypay location id')->after('status');
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_keypay_auth_details', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_keypay_auth_details', 'location_id')) {
                $table->dropColumn('location_id');
            }
        });
    }

}
