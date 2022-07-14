<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_role')) {
            Schema::create('tbl_role', function (Blueprint $table) {
                $table->unsignedSmallInteger('id')->autoIncrement();
                $table->unsignedSmallInteger('companyId')->index();
                $table->string('name',32);
                $table->unsignedTinyInteger('status');
                $table->unsignedTinyInteger('archive');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_role');
    }
}
