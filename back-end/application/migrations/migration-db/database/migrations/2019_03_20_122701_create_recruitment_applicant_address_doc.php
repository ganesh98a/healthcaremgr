<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantAddressDoc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_applicant_address')) {
            Schema::create('tbl_recruitment_applicant_address', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicant_id');
                $table->string('street',128);
                $table->string('city',64);
                $table->unsignedInteger('postal');
                $table->unsignedTinyInteger('state');
                $table->string('latitude',30);
                $table->string('longitude',30);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('primary_address');
            });
            DB::statement('ALTER TABLE `tbl_recruitment_applicant_address` CHANGE `postal` `postal` int(4) unsigned zerofill NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_applicant_address');
    }
}
