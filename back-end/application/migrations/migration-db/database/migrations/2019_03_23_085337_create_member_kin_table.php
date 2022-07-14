<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberKinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_kin')) {
            Schema::create('tbl_member_kin', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('memberId')->index('memberId');
                    $table->unsignedTinyInteger('primary_kin')->comment('1- Primary, 2- Secondary');
                    $table->string('firstname', 32);
                    $table->string('lastname', 32);
                    $table->string('relation', 24);
                    $table->string('phone', 20);
                    $table->string('email', 64);
                    $table->unsignedTinyInteger('archive')->comment('1- Delete');
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
        Schema::dropIfExists('tbl_member_kin');
    }
}
