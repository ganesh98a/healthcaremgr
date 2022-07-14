<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_app_feedback')) {
            Schema::create('tbl_app_feedback', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('memberid');
                $table->smallInteger('rating');
                $table->string('feedback',1000);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_app_feedback');
    }
}
