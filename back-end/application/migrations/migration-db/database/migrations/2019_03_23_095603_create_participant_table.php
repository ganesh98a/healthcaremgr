<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant')) {
            Schema::create('tbl_participant', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->string('username', 200);
                    $table->string('firstname', 32)->index('firstname');
                    $table->string('middlename', 32)->index('middlename');
                    $table->string('lastname', 32)->index('lastname');
                    $table->unsignedTinyInteger('gender')->comment('1- Male, 2- Female');
                    $table->string('profile_image');
                    $table->string('relation', 50);
                    $table->string('preferredname', 32)->index('preferredname');
                    $table->unsignedInteger('prefer_contact')->comment('1-for contact/2-for-email');
                    $table->date('dob');
                    $table->string('ndis_num', 15);
                    $table->string('medicare_num', 15);
                    $table->string('crn_num', 15);
                    $table->unsignedTinyInteger('referral')->comment('1- Yes, 0- No');
                    $table->string('referral_firstname', 200);
                    $table->string('referral_lastname', 200);
                    $table->string('referral_email', 100);
                    $table->string('referral_phone', 20);
                    $table->string('living_situation', 20);
                    $table->string('aboriginal_tsi', 20);
                    $table->unsignedInteger('oc_departments');
                    $table->unsignedInteger('houseId')->default(0)->index('houseId');
                    $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'))->index('created');
                    $table->unsignedTinyInteger('status')->index('status')->comment('1- Active, 0- Inactive');
                    $table->unsignedTinyInteger('portal_access')->comment('1- Yes, 0- No');
                    $table->unsignedTinyInteger('archive')->comment('0- not / 1 - yes');
                    $table->text('password', 65535);
                    $table->unsignedTinyInteger('loginattempt');
                    $table->string('token', 200);
                    $table->unsignedInteger('booking_status')->comment('1 - open/ 2 - close');
                    $table->date('booking_date');
                });
                DB::statement('ALTER TABLE `tbl_participant` ADD FULLTEXT `firstname_2` (`firstname`)');
                DB::statement('ALTER TABLE `tbl_participant` ADD FULLTEXT `middlename_2` (`middlename`)');
                DB::statement('ALTER TABLE `tbl_participant` ADD FULLTEXT `lastname_2` (`lastname`)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_participant');
    }
}
