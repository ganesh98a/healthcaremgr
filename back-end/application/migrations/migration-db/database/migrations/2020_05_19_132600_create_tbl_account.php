<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAccount extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_account', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('account_code', "255")->comment('is uniqe');

            $table->bigInteger('person_id')->unsigned()->comment('tbl_person.id')->nullable();
            $table->foreign('person_id')->references('id')->on('tbl_person')->onDelete('CASCADE');

            $table->string('account_name')->comment('first & last name / company name');
            $table->unsignedSmallInteger('status')->comment("0 = inactive, 1 = active");

            $table->unsignedInteger('created_by')->comment("tbl_member.id created by");
            $table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('CASCADE');

            $table->dateTime('created');
            $table->dateTime('updated');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });

        if (Schema::hasTable('tbl_account')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `account_before_insert_account_code`');
            DB::unprepared("CREATE TRIGGER `account_before_insert_account_code` BEFORE INSERT ON `tbl_account` FOR EACH ROW
                IF NEW.account_code IS NULL or NEW.account_code='' THEN
                SET NEW.account_code = (SELECT CONCAT('OS',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_account ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_account');
    }

}
