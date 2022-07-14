<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftIncidentTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_shift_incident_report', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('shift_id')->comment('tbl_shift.id');
            $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('incident_occur_today')->nullable()->comment('1-Yes,2-No');
            $table->unsignedInteger('incident_report')->nullable()->comment('1-Yes,2-No');

            $table->dateTime('created')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('updated')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

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
        Schema::table('tbl_shift_incident_report', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_incident_report', 'shift_id')) {
                $table->dropForeign(['shift_id']);
            }
        });

        Schema::dropIfExists('tbl_shift_incident_report');
    }
}
