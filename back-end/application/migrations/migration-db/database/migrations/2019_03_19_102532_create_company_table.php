<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_company')) {
            Schema::create('tbl_company', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name',128);
                $table->string('address',128);
                $table->string('city',64);
                $table->string('state',64);
                $table->string('email',64);
                $table->string('phone',20);
                $table->string('contact_person',64);
                $table->unsignedTinyInteger('status')->index()->nullable()->default(1);
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
        Schema::dropIfExists('tbl_company');
    }
}
