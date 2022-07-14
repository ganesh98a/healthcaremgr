<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowNullableTaskFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            $table->date('due_date')->nullable()->change();
            $table->string('assign_to', 30)->nullable()->change();
            $table->integer('crm_participant_id')->nullable()->change();
            $table->integer('related_to')->nullable()->change();
            $table->integer('related_type')->nullable()->change();
            // $table->bigInteger('entity_id')->nullable()->change();
            // $table->smallInteger('entity_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_participant_schedule_task', function (Blueprint $table) {
            //
        });
    }
}
