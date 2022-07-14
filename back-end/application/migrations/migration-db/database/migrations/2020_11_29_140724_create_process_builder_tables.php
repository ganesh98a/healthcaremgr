<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessBuilderTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin_process_event')) {
            Schema::create('tbl_admin_process_event', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('description', 255)->nullable();
                $table->string('object_name', 255)->nullable();
                $table->string('event_trigger', 255)->nullable();
                $table->string('criteria', 255)->nullable();
                $table->string('event_action', 255)->nullable();
                $table->bigInteger('email_template')->unsigned()->nullable()->comment('tbl_email_templates.id');
                $table->foreign('email_template')->references('id')->on('tbl_email_templates')->onDelete('cascade');
                $table->string('recipient', 255)->nullable();
                $table->unsignedInteger('status')->nullable()->comment('0 - InActive / 1 - Active');
                $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('reference id of tbl_member.id');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('updated_by')->nullable()->comment('reference id of tbl_member.id');
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_admin_process_event', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_admin_process_event', 'email_template')) {
                $table->dropForeign(['email_template']);
            if (Schema::hasColumn('tbl_admin_process_event', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_admin_process_event', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        }
    });
        Schema::dropIfExists('tbl_admin_process_event');
    }
}
