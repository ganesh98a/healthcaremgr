<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_contact', function (Blueprint $table) {
			$table->engine = 'InnoDB';
			
            $table->bigIncrements('id');
            $table->string('contact_number', 255);
			
			$table->bigInteger('person_id')->unsigned()->comment("tbl_person.id")->nullable();
			$table->foreign('person_id')->references('id')->on('tbl_person'); 
			
            $table->bigInteger('contact_type')->unsigned()->comment("tbl_contact_type.id")->nullable();;
			$table->foreign('contact_type')->references('id')->on('tbl_contact_type'); 
			
            $table->integer('contact_source')->unsigned()->comment('tbl_lead_source_code.id')->nullable();;
			$table->foreign('contact_source')->references('id')->on('tbl_lead_source_code'); 
			
			$table->string('street', 255);
			
			$table->integer('state')->unsigned()->comment('tbl_state.id')->nullable();;
			$table->foreign('state')->references('id')->on('tbl_state');
			
			$table->string('suburb', 100);
			$table->string('postcode', 10);
			
            $table->unsignedSmallInteger('status')->comment('0-Inactive/1-Active');
			
            $table->integer('created_by')->unsigned()->comment("tbl_member.id created by");
			$table->foreign('created_by')->references('id')->on('tbl_member'); 
			
            $table->dateTime('created');
            $table->dateTime('updated');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });
		
		if (Schema::hasTable('tbl_contact')) {
                DB::unprepared('DROP TRIGGER  IF EXISTS `contact_before_insert_contact_number`');
                DB::unprepared("CREATE TRIGGER `contact_before_insert_contact_number` BEFORE INSERT ON `tbl_contact` FOR EACH ROW
                IF NEW.contact_number IS NULL or NEW.contact_number='' THEN
                SET NEW.contact_number = (SELECT CONCAT('CT',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_contact ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
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
        Schema::dropIfExists('tbl_contact');
    }
}
