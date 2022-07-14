<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_action')) {
            Schema::create('tbl_recruitment_action', function (Blueprint $table) {
                $table->increments('id');
                $table->string('action_name',100);
                $table->unsignedInteger('user');
                $table->unsignedTinyInteger('action_type')->comment('0=group interview ,1= single');
                $table->datetime('start_datetime');
                $table->datetime('end_datetime');
                $table->unsignedTinyInteger('training_location');
                $table->unsignedTinyInteger('status')->comment('1- Active, 0- Inactive');
                $table->unsignedTinyInteger('mail_status')->comment('0=No , 1= Yes');
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
        Schema::dropIfExists('tbl_recruitment_action');
    }
}
