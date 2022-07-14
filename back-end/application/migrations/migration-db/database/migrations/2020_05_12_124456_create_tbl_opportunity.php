<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblOpportunity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_opportunity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('opportunity_number');
            $table->string('topic', 255);
            $table->unsignedInteger('opportunity_source')->comment('tbl_lead_source_code.id');
			$table->foreign('opportunity_source')->references('id')->on('tbl_lead_source_code')->onDelete('CASCADE'); 
			 
            $table->bigInteger('related_lead')->unsigned()->comment('tbl_leads.id')->nullable();
			$table->foreign('related_lead')->references('id')->on('tbl_leads')->onDelete('CASCADE'); 
			
            $table->unsignedInteger('opportunity_type')->comment('tbl_opportunity_type.id');
			$table->foreign('opportunity_type')->references('id')->on('tbl_opportunity_type')->onDelete('CASCADE'); 
			
            $table->unsignedInteger('neeed_support_plan')->comment('0 - No/1 - Yes');
            $table->bigInteger('account_person')->unsigned()->comment("tbl_person.id");
			$table->foreign('account_person')->references('id')->on('tbl_person')->onDelete('CASCADE');
			
            $table->double('amount', 10, 2);
            $table->unsignedInteger('owner')->comment('tbl_member.id admin id');;
			$table->foreign('owner')->references('id')->on('tbl_member')->onDelete('CASCADE'); 
			
            $table->unsignedInteger('opportunity_status')->comment("tbl_opportunity_status.id");
			$table->foreign('opportunity_status')->references('id')->on('tbl_opportunity_status')->onDelete('CASCADE'); 
			
            $table->unsignedInteger('created_by')->comment("tbl_member.id created by");
			$table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('CASCADE'); 
			
            $table->dateTime('created');
            $table->dateTime('updated');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });
		
		if (Schema::hasTable('tbl_opportunity')) {
                DB::unprepared('DROP TRIGGER  IF EXISTS `opportunity_before_insert_lead_number`');
                DB::unprepared("CREATE TRIGGER `opportunity_before_insert_lead_number` BEFORE INSERT ON `tbl_opportunity` FOR EACH ROW
                IF NEW.opportunity_number IS NULL or NEW.opportunity_number='' THEN
                SET NEW.opportunity_number = (SELECT CONCAT('OP',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_opportunity ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
            }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_opportunity');
    }
}
