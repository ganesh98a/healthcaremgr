<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminPhoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin_phone')) {
            Schema::create('tbl_admin_phone', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('adminId');
                $table->string('phone',20);
                $table->unsignedTinyInteger('primary_phone')->comment('1- Primary, 2- Secondary');
                $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data(delete)'); 
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
        Schema::dropIfExists('tbl_admin_phone');
    }
}
