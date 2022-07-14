<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadHistoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_lead_history')) {
            Schema::create('tbl_lead_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('lead_id')->unsigned()->comment('tbl_leads.id');
                $table->foreign('lead_id')->references('id')->on('tbl_leads')->onDelete('cascade');
                $table->unsignedInteger('created_by')->comment('the user who initiated the field change, or zero if initiated by the system');
                $table->foreign('created_by')->references('id')->on('tbl_member');          // do not cascade
                $table->dateTimeTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));    // not nullable
            });
        }

        if (!Schema::hasTable('tbl_lead_field_history')) {
            Schema::create('tbl_lead_field_history', function (Blueprint $table) {
                $fields = [
                    'lead_status', 'lead_topic', 'lead_owner', 'firstname', 'lastname', 'phone', 'email', 'lead_company', 'lead_source_code', 'lead_description', 'is_converted', 'converted_by', 'created_by','created'
                ];

                $table->bigIncrements('id');
                $table->bigInteger('history_id')->unsigned()->comment('the assosciated Lead history item');
                $table->foreign('history_id')->references('id')->on('tbl_lead_history')->onDelete('cascade');
                $table->bigInteger('lead_id')->unsigned()->comment('the associated Lead');
                $table->foreign('lead_id')->references('id')->on('tbl_leads')->onDelete('cascade');
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
        Schema::table('tbl_lead_field_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_lead_field_history', 'history_id')) {
                $table->dropForeign(['history_id']);
            }
            if (Schema::hasColumn('tbl_lead_field_history', 'lead_id')) {
                $table->dropForeign(['lead_id']);
            }
        });
        Schema::table('tbl_lead_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_lead_history', 'lead_id')) {
                $table->dropForeign(['lead_id']);
            }
            if (Schema::hasColumn('tbl_lead_history', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_lead_field_history');
        Schema::dropIfExists('tbl_lead_history');
    }
}
