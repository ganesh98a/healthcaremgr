<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutOfOfficeMessage extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_out_of_office_message', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('adminId')->comment('out of office message for (primary key for tbl_member)');
            $table->date('from_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('concactId')->nullable()->comment('error msg column value save when import csv');
            $table->text('default_message');
            $table->text('additional_message');
            $table->dateTime('created');
            $table->dateTime('updated');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_out_of_office_message');
    }

}
