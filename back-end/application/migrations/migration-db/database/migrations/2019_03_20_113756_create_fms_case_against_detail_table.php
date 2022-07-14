<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseAgainstDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_against_detail')) {
            Schema::create('tbl_fms_case_against_detail', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('caseId');
                $table->unsignedInteger('against_category')->comment('1- member of public,2- Member, 3- Participant, 4- ONCALL (General), 5- ONCALL User/Admin');
                $table->unsignedInteger('against_by')->comment('Member or Participant id');
                $table->string('against_first_name',150)->nullable()->comment('when against_category = 1');
                $table->string('against_last_name',150)->nullable()->comment('when against_category = 1');
                $table->string('against_email',150)->nullable()->comment('when against_category = 1');
                $table->string('against_phone',150)->nullable()->comment('when against_category = 1');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_fms_case_against_detail');
    }
}
