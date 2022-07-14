<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRoleAddRoleKey extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_role', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_role', 'role_key')) {
                $table->string('role_key', 150)->default('')->after('name')->comment('unique key');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_role', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_role', 'role_key')) {
                $table->dropColumn('role_key');
            }
        });
    }

}
