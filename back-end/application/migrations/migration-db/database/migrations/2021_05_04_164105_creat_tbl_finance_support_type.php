<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatTblFinanceSupportType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_support_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',255)->nullable();
            $table->string('key_name',255)->nullable();
            $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_support_type');
    }
}
