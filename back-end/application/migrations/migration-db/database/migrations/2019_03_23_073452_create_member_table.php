<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member')) {
            Schema::create('tbl_member', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('companyId')->index('companyId');
                    $table->string('firstname', 32)->index('username');
                    $table->string('lastname', 32)->index('lastname');
                    $table->string('middlename', 32);
                    $table->string('preferredname', 64);
                    $table->string('pin', 64);
                    $table->string('profile_image');
                    $table->unsignedTinyInteger('user_type')->index('user_type')->comment('0- Support Coordinator, 1- Member');
                    $table->string('deviceId', 64);
                    $table->unsignedTinyInteger('status')->default(1)->index('status')->comment('1- Active, 0- Inactive');
                    $table->string('prefer_contact', 6);
                    $table->date('dob');
                    $table->unsignedTinyInteger('gender')->default(1)->comment('1- Male, 2- Female');
                    $table->unsignedTinyInteger('push_notification_enable')->comment('1- Enable, 0- Disable');
                    $table->unsignedTinyInteger('enable_app_access')->comment('1- Enable, 0- Disable');
                    $table->unsignedTinyInteger('dwes_confirm')->comment('1- Confirm, 0- not Confirm');
                    $table->unsignedTinyInteger('archive')->comment('1- Delete');
                    $table->dateTime('created');
                    $table->unsignedTinyInteger('loginattempt');
                });
                DB::statement('ALTER TABLE `tbl_member` ADD FULLTEXT `firstname` (`firstname`)');
                DB::statement('ALTER TABLE `tbl_member` ADD FULLTEXT `middlename` (`middlename`)');
                DB::statement('ALTER TABLE `tbl_member` ADD FULLTEXT `lastname_2` (`lastname`)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_member');
    }
}
