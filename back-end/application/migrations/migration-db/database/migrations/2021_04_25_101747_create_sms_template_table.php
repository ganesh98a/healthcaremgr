<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sms_template', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->comment('Template name');
            $table->string('short_description', 255)->comment('short description');
            $table->text('content')->comment('SMS Content');
            $table->enum('folder', ['public', 'private'])->default('public');
            $table->boolean('archive')->default(0)->comment('Soft delete Not=0, Yes=1');
            $table->unsignedBigInteger('created_by')->comment('tbl_members.id');
            $table->unsignedBigInteger('updated_by')->comment('tbl_members.id');
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
        Schema::dropIfExists('tbl_sms_template');
    }
}
