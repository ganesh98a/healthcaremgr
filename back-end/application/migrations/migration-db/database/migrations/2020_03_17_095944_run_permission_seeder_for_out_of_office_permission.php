<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunPermissionSeederForOutOfOfficePermission extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_permission', function (Blueprint $table) {
            $seeder = new PermissionSeeder();
            $seeder->run();

            $Roleseeder = new RolePermissionSeeder();
            $Roleseeder->run();

            $Role = new RoleSeeder();
            $Role->run();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_permission', function (Blueprint $table) {
            //
        });
    }

}
