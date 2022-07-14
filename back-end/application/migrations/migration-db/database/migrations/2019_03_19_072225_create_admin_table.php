<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin')) 
        {
            Schema::create('tbl_admin', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('companyId')->index()->default(0);
                $table->string('username',20)->index();
                $table->string('password',64);
                $table->text('pin');
                $table->string('firstname',32)->index();
                $table->string('lastname',32)->index();
                $table->string('profile',100);
                $table->string('position',50);
                $table->string('department',50);
                $table->unsignedTinyInteger('status')->index()->default(1)->comment('1- Active, 0- Inactive');
                $table->string('background',64);
                $table->unsignedTinyInteger('gender')->default(1)->comment('1- Male, 2- Female');
                $table->string('token',255);
                $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data');
                $table->timestamp('created');
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
        Schema::dropIfExists('tbl_admin');
    }
}
