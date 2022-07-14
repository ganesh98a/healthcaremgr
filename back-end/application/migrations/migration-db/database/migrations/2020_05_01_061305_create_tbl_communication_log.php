<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCommunicationLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {	
		
		if (!Schema::hasTable('tbl_communication_log')) {
			
			Schema::create('tbl_communication_log', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('userId')->comment("primary key of tables");
				$table->unsignedInteger('user_type')->comment("1-Applicant");
                $table->tinyInteger('log_type')->comment('1 sms/2 - email/3 - phone');
				$table->string('from', 255);
				$table->string('title', 255);
				$table->text('communication_text');
				$table->unsignedInteger('send_by')->default('0')->comment('tbl_member.id');
                $table->dateTime('created')->default('0000-00-00 00:00:00');;
			});
			
			Schema::dropIfExists('tbl_recruitment_applicant_communication_log');
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_communication_log');
    }
}
