<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNdisSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ndis_support', function (Blueprint $table) {
          $table->increments('support_id');
          $table->unsignedInteger('registration_group_number');
          $table->string('registration_group_name',500);
          $table->unsignedInteger('supoort_category_number');
          $table->string('support_category_name',500);
          $table->string('support_item_number',50);
          $table->string('support_item_name',500);
          $table->string('support_item_description',1000);
          $table->string('unit',1000);
          $table->string('price_controlled',20);
          $table->string('quote_required',20);
          $table->string('national_non_remote',20);
          $table->string('national_remote',20);
          $table->string('national_very_remote',20);
          $table->string('amount',50);
          $table->dateTime('created')->default('0000-00-00 00:00:00');
          $table->timestamp('updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ndis_support');
    }
}
