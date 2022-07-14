<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       if (!Schema::hasTable('tbl_participant_line_items')) {
            Schema::create('tbl_participant_line_items', function(Blueprint $table)
                {
                    $table->increments('id');
					$table->unsignedInteger('plan_id')->default(0)->comment('participant_plan auto increment id');
					$table->unsignedInteger('line_item_id')->default(0)->comment('tbl_finance_line_item auto increment id');
					$table->double('amount', 14, 2);
					$table->datetime('updated')->default(DB::raw('CURRENT_TIMESTAMP'));        
					$table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                    $table->unsignedTinyInteger('archive')->comment('0- not /1 - archive');
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
        Schema::dropIfExists('tbl_participant_line_items');
    }
}
