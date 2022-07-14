<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblEmailTemplatesAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {	
		if (!Schema::hasTable('tbl_email_templates_attachment')) {
            Schema::create('tbl_email_templates_attachment', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('templateId')->comment("tbl_email_templates.id");
                $table->foreign('templateId')->references('id')->on('tbl_email_templates');
				$table->text('filename', 255);
                $table->dateTime('created')->nullable();
				$table->unsignedSmallInteger('archive');
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
        Schema::dropIfExists('tbl_email_templates_attachment');
    }
}
