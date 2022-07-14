<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_email_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->comment('Template name');
            $table->text('content')->comment('Template body. Accepts html content');
            $table->boolean('status')->comment('draft=0, active=1');
            $table->boolean('archive')->comment('Soft delete Not=0, Yes=1');
            $table->unsignedBigInteger('created_by')->comment('tbl_members.id');

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
        Schema::dropIfExists('tbl_email_templates');
    }
}
