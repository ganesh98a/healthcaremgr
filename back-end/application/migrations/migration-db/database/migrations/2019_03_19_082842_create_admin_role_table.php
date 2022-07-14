<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin_role')) {
            Schema::create('tbl_admin_role', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedSmallInteger('roleId')->index();
                $table->unsignedInteger('adminId')->index();
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
        Schema::dropIfExists('tbl_admin_role');
    }
}
