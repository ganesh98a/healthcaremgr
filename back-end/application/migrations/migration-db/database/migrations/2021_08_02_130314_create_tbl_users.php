<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_users')) {
            Schema::create('tbl_users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username',100)->comment("user email");
                $table->text('password')->nullable();
                $table->unsignedInteger('user_type')->nullable()->comment('1-admin/2-member/3-organisation/4-client');
                $table->text('password_token')->nullable();
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();               
            });
        }

        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'uuid')) {
                $table->unsignedBigInteger('uuid')->nullable()->after("ocp_id");
            } 
        });

        Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'uuid')) {
                $table->unsignedBigInteger('uuid')->nullable()->after("contact_code");
            } 
        });

        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant', 'uuid')) {
                $table->unsignedBigInteger('uuid')->nullable()->after("id");
            } 
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tbl_users')) {
            Schema::dropIfExists('tbl_users');
        }
    }
}
