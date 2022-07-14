<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_address')) {
            Schema::create('tbl_member_address', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('memberId')->default(0)->index('memberId');
                    $table->unsignedTinyInteger('primary_address')->comment('1- Primary, 2- Secondary');
                    $table->string('street', 128);
                    $table->string('city', 64);
                    $table->unsignedInteger('postal')->unsigned();
                    $table->unsignedInteger('state');
                    $table->string('lat', 200);
                    $table->string('long', 200);
                    $table->unsignedTinyInteger('archive')->default(0)->comment('1- Delete');
                });
                DB::statement('ALTER TABLE `tbl_member_address` CHANGE `postal` `postal` int(4) unsigned zerofill NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_member_address');
    }
}
