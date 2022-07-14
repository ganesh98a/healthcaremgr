<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAutomaticEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_automatic_email', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('moduleId')->comment('tbl_module_title.id');
            $table->string('email_name', 255);
            $table->string('email_key_name', 255)->comment('uniqe name');
            $table->unsignedInteger('templateId')->default('0')->comment('tbl_email_templates.id');
            $table->unsignedInteger('assign_by')->default('0')->comment('tbl_member.id');
            $table->dateTime('assign_at')->comment("assign when email template");
            $table->dateTime('created');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });
		
		$seeder = new AutomaticEmail();
		$seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_automatic_email');
    }
}
