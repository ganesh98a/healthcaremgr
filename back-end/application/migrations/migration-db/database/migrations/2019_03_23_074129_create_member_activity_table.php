<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_activity')) {
            Schema::create('tbl_member_activity', function(Blueprint $table)
                {
                    $table->unsignedInteger('activityId')->index('activityId');
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
        Schema::dropIfExists('tbl_member_activity');
    }
}
