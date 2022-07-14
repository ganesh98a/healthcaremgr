<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeyContraintForMemberApplicantTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'uuid')){
                // add foreign key
                $table->unsignedInteger('uuid')->nullable()->comment('tbl_users.id')->change();
                $table->foreign('uuid')->references('id')->on('tbl_users');
            }
        });

        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'uuid')){
                $table->unsignedInteger('uuid')->nullable()->comment('tbl_users.id')->change();
                // add foreign key
                $table->foreign('uuid')->references('id')->on('tbl_users');
            }
        });

        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant', 'uuid')){
                $table->unsignedInteger('uuid')->nullable()->comment('tbl_users.id')->change();
                // add foreign key
                $table->foreign('uuid')->references('id')->on('tbl_users');
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
        //
    }
}
