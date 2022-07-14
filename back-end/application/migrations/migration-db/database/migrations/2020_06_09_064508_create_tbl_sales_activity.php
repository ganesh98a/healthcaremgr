<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSalesActivity extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_sales_activity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedSmallInteger('activity_type')->comment('1 - task,2 - email,3 - call,4 - note');;
            $table->bigInteger('entity_id')->unsigned();
            $table->unsignedSmallInteger('entity_type')->unsigned()->comment('1-contact, 2-organisation, 3-opportunity');

            $table->integer('taskId')->unsigned();
            $table->foreign('taskId')->references('id')->on('tbl_crm_participant_schedule_task')->onUpdate('cascade')->onDelete('cascade');

            $table->text('description');

            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('created');
            $table->unsignedSmallInteger('archive')->unsigned()->comment('0-Not/1-Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_sales_activity');
    }

}
