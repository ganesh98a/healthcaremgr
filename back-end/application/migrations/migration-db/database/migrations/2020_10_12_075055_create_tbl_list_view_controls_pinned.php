<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblListViewControlsPinned extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_list_view_controls_pinned')) {
            Schema::create('tbl_list_view_controls_pinned', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('admin_id');
                $table->unsignedInteger('pinned_id')->default(0);
                $table->unsignedInteger('related_type')->comment('1-contact/2-org/3-tasks/4-leads/5-opp/6-need-ass/7-risk-ass/8-service'); 
                $table->boolean('archive')->default(0)->comment('0- not archive, 1- archive data(delete)'); 
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
        Schema::dropIfExists('tbl_list_view_controls_pinned');
    }
}
