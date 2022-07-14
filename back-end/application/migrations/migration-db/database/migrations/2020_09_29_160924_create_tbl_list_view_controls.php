<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblListViewControls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_list_view_controls', function (Blueprint $table) {
            $table->increments('id');
            $table->text('list_name',255);
            $table->unsignedInteger('view_to_one_user')->default(0)->comment('0 - false / 1 - true'); 
            $table->unsignedInteger('view_to_all_user')->default(0)->comment('0 - false / 1 - true');           
            $table->text('related_type')->comment('1-contact/2-org/3-tasks/4-leads/5-opp/6-need-ass/7-risk-ass/8-service'); 
            $table->longText('filter_data')->nullable();;
            $table->text('filter_logic',255)->nullable();
            $table->unsignedInteger('filter_operand')->nullable();
            $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_list_view_controls');
    }
}
