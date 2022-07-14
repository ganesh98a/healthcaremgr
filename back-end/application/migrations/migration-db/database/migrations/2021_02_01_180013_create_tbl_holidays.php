<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblHolidays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_holidays')) {
            Schema::create('tbl_holidays', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('holiday_name');
				$table->bigInteger('type')->unsigned();
                $table->string('location', 255)->nullable();
				$table->bigInteger('status')->unsigned()->nullable();
				$table->string('day', 255)->nullable();
				$table->dateTime('date')->nullable();
				$table->dateTime('created')->nullable();
				$table->unsignedInteger('created_by')->nullable();
				$table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
				$table->dateTime('updated')->nullable();
				$table->unsignedInteger('updated_by')->nullable()->comment('reference id of tbl_member.id');
				$table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
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
        Schema::dropIfExists('tbl_holidays');
    }
}
