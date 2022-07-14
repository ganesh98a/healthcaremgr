<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSalesActivityEmailRecipient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sales_activity_email_recipient', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('activity_id')->comment("reference id of tbl_sales_activity table");
            $table->unsignedInteger('type')->comment("1 - From, 2 - To, 3 - Cc, 4 - Bcc");
            $table->unsignedInteger('recipient')->comment("reference id of tbl_person table");
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
        Schema::dropIfExists('tbl_sales_activity_email_recipient');
    }
}
