<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNeedAssessmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_need_assessment_status')) {
            Schema::create('tbl_need_assessment_status', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('key_name', 255);
                $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
            });
        }

        if (!Schema::hasTable('tbl_need_assessment')) {
            Schema::create('tbl_need_assessment', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title',500);
                $table->string('need_assessment_number',20);
                $table->unsignedInteger('owner')->comment("tbl_member.id, only admin");
                $table->foreign('owner')->references('id')->on('tbl_member')->onDelete('CASCADE');

                $table->unsignedInteger('status')->comment("tbl_need_assessment_status.id");
                $table->foreign('status')->references('id')->on('tbl_need_assessment_status')->onDelete('CASCADE');

                $table->unsignedInteger('account_person')->comment("tbl_person.id / tbl_organisaition.id");
                $table->unsignedInteger('account_type')->comment("1-Person/2-organisation");

                $table->timestamp('created')->useCurrent();
                $table->integer('created_by')->unsigned()->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('CASCADE');

                $table->dateTime('updated');
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onDelete('CASCADE');
                
                $table->unsignedSmallInteger('archive')->default(0)->comment("0-No/1-Yes");
            });
        }

        if (Schema::hasTable('tbl_need_assessment')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `need_assessment_before_insert_need_assessment_number`');
            DB::unprepared("CREATE TRIGGER `need_assessment_before_insert_need_assessment_number` BEFORE INSERT ON `tbl_need_assessment` FOR EACH ROW
            IF NEW.need_assessment_number IS NULL or NEW.need_assessment_number='' THEN
            SET NEW.need_assessment_number = (SELECT CONCAT('NA',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_need_assessment ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
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
        Schema::dropIfExists('tbl_need_assessment');
        Schema::dropIfExists('tbl_need_assessment_status');
    }
}
