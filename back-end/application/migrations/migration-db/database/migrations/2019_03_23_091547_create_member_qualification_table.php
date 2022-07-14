<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberQualificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_qualification')) {
            Schema::create('tbl_member_qualification', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('memberId')->index('memberId');
                    $table->date('expiry');
                    $table->string('title', 64);
                    $table->unsignedInteger('type')->comment('1- Police Check, 2- WWCC, 3- First Aid, 4- Fire Safety,5- CPR, 6- Anaphylaxis');
                    $table->dateTime('created');
                    $table->unsignedTinyInteger('can_delete')->comment('1- Yes, 0- No');
                    $table->string('filename', 64);
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
        Schema::dropIfExists('tbl_member_qualification');
    }
}
