<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberPlaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_place')) {
            Schema::create('tbl_member_place', function(Blueprint $table)
                {
                    $table->unsignedInteger('placeId')->index('placeId');
                    $table->unsignedInteger('memberId')->index('memberId');
                    $table->unsignedTinyInteger('type')->comment('1- Favourite, 2- Least Favourite');
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
        Schema::dropIfExists('tbl_member_place');
    }
}
