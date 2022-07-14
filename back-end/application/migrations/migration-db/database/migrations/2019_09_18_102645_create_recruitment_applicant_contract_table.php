<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_applicant_contract')) {
            Schema::create('tbl_recruitment_applicant_contract', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('task_applicant_id')->comment('auto increment id of tbl_recruitment_task_applicant table.');
                $table->string('envelope_id',255)->nullable()->comment('docusign api unique id');
                $table->string('unsigned_file',255)->nullable()->comment('file path');
                $table->string('signed_file',255)->nullable()->comment('file path');
                $table->unsignedSmallInteger('signed_status')->default('0')->comment('0- mean not signed yet,1-sigend');
                $table->dateTime('signed_date')->default('0000-00-00 00:00:00');
                $table->dateTime('send_date')->default('0000-00-00 00:00:00');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->unsignedTinyInteger('archive')->default('0');
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
        Schema::dropIfExists('tbl_recruitment_applicant_contract');
    }
}
