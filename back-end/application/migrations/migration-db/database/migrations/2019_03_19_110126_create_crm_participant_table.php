<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant')) {
            Schema::create('tbl_crm_participant', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username',200);
                $table->string('firstname',32)->index();
                $table->string('middlename',32)->index();
                $table->string('lastname',32)->index();
                $table->unsignedTinyInteger('gender')->nullable()->default(1)->comment('1- Male, 2- Female');
                $table->string('profile_image',255);
                $table->string('relation',50);
                $table->string('preferredname',32)->index();
                $table->unsignedTinyInteger('prefer_contact')->comment('1-for contact/2-for-email');
                $table->date('dob');
                $table->string('ndis_num',15);
                $table->string('medicare_num',15);
                $table->unsignedTinyInteger('behavioural_support_plan')->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('referral')->comment('1- Yes, 0- No');
                $table->string('referral_firstname',200);
                $table->string('referral_lastname',200);
                $table->string('referral_email',100);
                $table->string('referral_phone',20);
                $table->string('living_situation',200);
                $table->string('aboriginal_tsi',20)->nullable();
                $table->unsignedTinyInteger('oc_departments')->nullable();
                $table->unsignedInteger('houseId')->index()->nullable()->default(0);
                $table->timestamp('created')->useCurrent();
                $table->unsignedTinyInteger('status')->index()->comment('1- Active, 0- Inactive');
                $table->unsignedTinyInteger('other_relevant_plans')->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data(delete)');
                $table->string('assigned_to',64);
                $table->unsignedTinyInteger('action_status')->comment('1 - Phone Screening, 2- Call');
                $table->unsignedInteger('booking_status')->comment('1 - Pending Contact, 2- Unassigned, 3-Successful, 4-Processing, 5- Rejected');
                $table->date('booking_date')->nullable();
                $table->string('referral_org',30)->nullable();
                $table->string('referral_relation',30)->nullable();
                $table->unsignedTinyInteger('marital_status')->comment('1-Married, 2-Single, 3-Divorced, 4-Widowed');
            });
            DB::statement('ALTER TABLE `tbl_crm_participant` ADD FULLTEXT `firstname_2` (`firstname`)');
            DB::statement('ALTER TABLE `tbl_crm_participant` ADD FULLTEXT `middlename_2` (`middlename`)');
            DB::statement('ALTER TABLE `tbl_crm_participant` ADD FULLTEXT `lastname_2` (`lastname`)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_crm_participant');
    }
}
