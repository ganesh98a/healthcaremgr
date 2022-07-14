<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrganisationUserCredentials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("tbl_organisation", function(Blueprint $table) {
            $table->string("username")->nullable();
            $table->string("password")->nullable();
            $table->string("password_reset_token")->nullable();
            $table->boolean("is_first_time_login")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("tbl_organisation", function(Blueprint $table) {
            $table->dropColumn(["username", "password", "password_reset_token", "is_first_time_login"]);
        });
    }
}
