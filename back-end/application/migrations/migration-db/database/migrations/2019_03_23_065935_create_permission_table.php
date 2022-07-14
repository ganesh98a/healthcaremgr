<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_permission')) {
            Schema::create('tbl_permission', function (Blueprint $table) {
                $table->increments('id');
                $table->smallInteger('companyId')->default('0');
                $table->string('permission',64);
                $table->string('title',64);
                $table->unsignedTinyInteger('pin_type')->comment('it help for pin token verify check')->default('0');
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
        Schema::dropIfExists('tbl_permission');
    }
}
