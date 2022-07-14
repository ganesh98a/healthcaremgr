<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftTimesheetAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_shift_timesheet_attachment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_id')->comment("reference id of tbl_shift table");
            $table->unsignedInteger('member_id')->comment("reference id of tbl_member table");
            $table->string('filename',255)->nullable();
            $table->string('file_path',255)->nullable();
            $table->string('file_type',255)->nullable();
            $table->string('file_size',255)->nullable();
            $table->string('file_ext',64)->nullable();
            $table->text('aws_object_uri')->nullable();
            $table->text('aws_response')->nullable();
            $table->unsignedInteger('aws_uploaded_flag')->default(0)->nullable()->comment('1 - Yes / 0 - No');
            $table->text('aws_file_version_id')->nullable()->comment('it is used to get the file with version if duplicated');
            $table->boolean('archive')->default(0)->comment('Soft delete Not=0, Yes=1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_shift_timesheet_attachment');
    }
}
