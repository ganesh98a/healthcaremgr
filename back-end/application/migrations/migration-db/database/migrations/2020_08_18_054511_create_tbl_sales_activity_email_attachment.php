<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSalesActivityEmailAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sales_activity_email_attachment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('activity_id')->comment("reference id of tbl_sales_activity table");
            $table->string('filename',255)->nullable();
            $table->string('file_path',255)->nullable();
            $table->string('file_type',255)->nullable();
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
        Schema::dropIfExists('tbl_sales_activity_email_attachment');
    }
}
