<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpportunityHistoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {       
        Schema::create('tbl_opportunity_history', function (Blueprint $table) {           
            $table->bigIncrements('id');
            $table->bigInteger('opportunity_id')->unsigned()->comment('the assosciated opportunity');
            $table->foreign('opportunity_id')->references('id')->on('tbl_opportunity')->onDelete('cascade');
            $table->unsignedInteger('created_by')->comment('the user who initiated the field change, or zero if initiated by the system');
            $table->foreign('created_by')->references('id')->on('tbl_member');          // do not cascade
            $table->dateTimeTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));    // not nullable
        });

        Schema::create('tbl_opportunity_field_history', function (Blueprint $table) {
            $fields = [
                    'created', 'converted', 'topic', 'opportunity type', 'owner', 'status', 'source', 'amount', 'description'
                ];

            $table->bigIncrements('id');
            $table->bigInteger('history_id')->unsigned()->comment('the assosciated opportunity history item');
            $table->foreign('history_id')->references('id')->on('tbl_opportunity_history')->onDelete('cascade');
            $table->bigInteger('opportunity_id')->unsigned()->comment('the assosciated opportunity');
            $table->foreign('opportunity_id')->references('id')->on('tbl_opportunity')->onDelete('cascade');
            $table->enum('field', $fields);
            $table->mediumText('value')->comment('current field value');                    // the JSON_VALID function is automatically included for types using the JSON alias - https://mariadb.com/kb/en/json-data-type/
            $table->mediumText('prev_val')->comment('previous field value')->nullable();    // as above    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_opportunity_field_history');
        Schema::dropIfExists('tbl_opportunity_history');
    }
}
