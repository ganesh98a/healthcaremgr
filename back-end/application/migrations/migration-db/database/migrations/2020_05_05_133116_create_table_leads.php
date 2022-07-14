<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_leads')) {
            Schema::create('tbl_leads', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('lead_number', 255)->comment('autogenrate lead number');
                $table->unsignedInteger('lead_status')->default(0)->comment('tbl_lead_status auto increments id')->index();
                $table->string('lead_topic', 255)->nullable()->comment('lead topic');
                $table->unsignedInteger('lead_owner')->default(0)->comment('tbl_member auto increments id')->index();
                $table->unsignedBigInteger('person_id')->comment('tbl_person auto increments id for lead person user id');
                $table->text('lead_description')->nullable()->comment('lead description');
                $table->string('lead_company',255)->nullable()->comment('lead company name or details');
                $table->unsignedInteger('lead_source_code')->default(0)->comment('tbl_lead_source_code auto increments id')->index();
                $table->unsignedSmallInteger('lead_converted')->default(0)->comment('not converted=0, converted=1');
                $table->unsignedSmallInteger('archive')->default(0)->comment('no=0, yes=1')->index();
                $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedInteger('created_by')->default(0)->comment('tbl_member auto increments id');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')); // let DB create updated timestamps instead of application code
                $table->unsignedInteger('updated_by')->default(0)->comment('tbl_member auto increments id');
                $table->foreign('person_id')->references('id')->on('tbl_person')->onDelete('CASCADE'); // destroy related row if a row in tbl_person.id is also destroyed
                $table->foreign('created_by')->references('id')->on('tbl_member');
            
            });

            if (Schema::hasTable('tbl_leads')) {
                DB::unprepared('DROP TRIGGER  IF EXISTS `leads_before_insert_lead_number`');
                DB::unprepared("CREATE TRIGGER `leads_before_insert_lead_number` BEFORE INSERT ON `tbl_leads` FOR EACH ROW
                IF NEW.lead_number IS NULL or NEW.lead_number='' THEN
                SET NEW.lead_number = (SELECT CONCAT('LD',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_leads ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_leads');
    }
}
