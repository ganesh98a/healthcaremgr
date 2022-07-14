<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunPermissionAndPermissionRoleSeederForFixFinanceAdminIssue extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
//        $seeder = new PermissionSeeder();
//        $seeder->run();

//        $Roleseeder = new RolePermissionSeeder();
//        $Roleseeder->run();
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
