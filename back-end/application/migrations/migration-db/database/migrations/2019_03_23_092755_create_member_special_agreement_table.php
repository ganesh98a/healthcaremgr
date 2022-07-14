<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberSpecialAgreementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_special_agreement')) {
            Schema::create('tbl_member_special_agreement', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('memberId')->index('memberId');
                    $table->string('title', 200);
                    $table->date('expiry');
                    $table->dateTime('created');
                    $table->string('filename', 100);
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
        Schema::dropIfExists('tbl_member_special_agreement');
    }
}
