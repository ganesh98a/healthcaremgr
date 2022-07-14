<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin_login')) {
            Schema::create('tbl_admin_login', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('adminId');
                $table->string('ip_address',100);
                $table->text('token');
                $table->timestamp('created')->useCurrent();
                $table->timestamp('updated')->default('0000-00-00 00:00:00');
                $table->text('pin')->nullable();
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
        Schema::dropIfExists('tbl_admin_login');
    }
}
