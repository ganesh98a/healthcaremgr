<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminLoginHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin_login_history')) {
            Schema::create('tbl_admin_login_history', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('adminId');
                $table->string('ip_address',100);
                $table->text('details');
                $table->timestamp('login_time')->default('0000-00-00 00:00:00');
                $table->timestamp('last_access')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('status')->comment('1- active / 2 - deactive');
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
        Schema::dropIfExists('tbl_admin_login_history');
    }
}
