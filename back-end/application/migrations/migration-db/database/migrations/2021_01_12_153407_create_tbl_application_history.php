<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblApplicationHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_application_history')) {
            Schema::create('tbl_application_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('application_id')->unsigned();
                $table->unsignedInteger('created_by')->comment('the user who initiated the field change, or zero if initiated by the system');
                $table->foreign('created_by')->references('id')->on('tbl_member');          // do not cascade
                $table->dateTimeTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));    // not nullable
            });
        }

        if (!Schema::hasTable('tbl_application_field_history')) {
            Schema::create('tbl_application_field_history', function (Blueprint $table) {
                $fields = [
                    'owner', 'status', 'created_by'];

                $table->bigIncrements('id');
                $table->bigInteger('history_id')->unsigned()->comment('the assosciated application history item');
                $table->foreign('history_id')->references('id')->on('tbl_application_history')->onDelete('cascade');
                $table->integer('application_id')->unsigned();
                $table->enum('field', $fields);
                $table->mediumText('value')->comment('current field value');
                $table->mediumText('prev_val')->comment('previous field value')->nullable();
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
        Schema::dropIfExists('tbl_application_history');
        Schema::dropIfExists('tbl_application_field_history'); 
    }
}
