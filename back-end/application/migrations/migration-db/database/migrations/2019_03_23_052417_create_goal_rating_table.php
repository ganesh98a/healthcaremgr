<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoalRatingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_goal_rating')) {
            Schema::create('tbl_goal_rating', function(Blueprint $table)
            {
                $table->increments('id');
                $table->unsignedInteger('rating');
                $table->string('name', 200);
                $table->unsignedTinyInteger('archive');
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
        Schema::dropIfExists('tbl_goal_rating');
    }
}
