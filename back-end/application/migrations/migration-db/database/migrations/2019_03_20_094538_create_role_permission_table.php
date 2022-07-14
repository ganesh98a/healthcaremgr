<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolePermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_role_permission')) {
        Schema::create('tbl_role_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('roleId')->index();
            $table->unsignedInteger('permission')->index();
        });
    }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_role_permission');
    }
}
