<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminPinTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin_pin_token')) {
            Schema::create('tbl_admin_pin_token', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('adminId');
                $table->unsignedInteger('token_id')->comment('tbl_admin_login primary key');
                $table->text('pin')->comment('generated jwt token');
                $table->unsignedTinyInteger('token_type')->comment('1 for fms, 2 for admin, 3 for incident');
                $table->string('ip_address',64);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_admin_pin_token');
    }
}
