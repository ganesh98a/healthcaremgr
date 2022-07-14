<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpportunityItemHistoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_opportunity_item_history')) {
            Schema::create('tbl_opportunity_item_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('opportunity_id')->unsigned()->comment('tbl_opportunity.id');
                $table->foreign('opportunity_id')->references('id')->on('tbl_opportunity')->onDelete('cascade');
                $table->integer('opportunity_item_id')->unsigned()->comment('tbl_opportunity_items.id');
                $table->foreign('opportunity_item_id')->references('id')->on('tbl_opportunity_items')->onDelete('cascade');
                $table->unsignedInteger('created_by')->comment('the user who initiated the field change, or zero if initiated by the system');
                $table->foreign('created_by')->references('id')->on('tbl_member');          // do not cascade
                $table->dateTimeTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));    // not nullable
            });
        }

        if (!Schema::hasTable('tbl_opportunity_item_field_history')) {
            Schema::create('tbl_opportunity_item_field_history', function (Blueprint $table) {
                $fields = [
                    'quantity', 'amount', 'archive', 'created', 'updated_by'
                ];

                $table->bigIncrements('id');
                $table->bigInteger('history_id')->unsigned()->comment('tbl_opportunity_item_history.id');
                $table->foreign('history_id')->references('id')->on('tbl_opportunity_item_history')->onDelete('cascade');
                $table->integer('opportunity_item_id')->unsigned()->comment('tbl_opportunity_items.id');
                $table->foreign('opportunity_item_id')->references('id')->on('tbl_opportunity_items')->onDelete('cascade');
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
        Schema::table('tbl_opportunity_item_field_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity_item_field_history', 'history_id')) {
                $table->dropForeign(['history_id']);
            }
            if (Schema::hasColumn('tbl_opportunity_item_field_history', 'opportunity_item_id')) {
                $table->dropForeign(['opportunity_item_id']);
            }
        });
        Schema::table('tbl_opportunity_item_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity_item_history', 'opportunity_id')) {
                $table->dropForeign(['opportunity_id']);
            }
            if (Schema::hasColumn('tbl_opportunity_item_history', 'opportunity_item_id')) {
                $table->dropForeign(['opportunity_item_id']);
            }
            if (Schema::hasColumn('tbl_opportunity_item_history', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_opportunity_item_field_history');
        Schema::dropIfExists('tbl_opportunity_item_history');
    }
}
