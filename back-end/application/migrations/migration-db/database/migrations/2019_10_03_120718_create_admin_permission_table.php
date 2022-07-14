<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminPermissionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_admin_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('permissionId')->comment('primary key of tbl_permission');
            $table->unsignedInteger('roleId')->comment('primary key of tbl_role/ and its optional');
            $table->unsignedInteger('adminId')->comment('primary key of tbl_member');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_admin_permission');
    }

}
