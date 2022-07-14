<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAdminApiLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_admin_api_log')) {
            Schema::create('tbl_admin_api_log', function (Blueprint $table) {
                $table->increments('id');
                
                $table->unsignedInteger('api_id')->default('1')->comment('1 = keypay, 2 = privacy idea');
                $table->text('api_url');
                $table->unsignedInteger('status')->default('1')->comment('1 = success, 2 = error');
                $table->text('data_in')->nullable();
                $table->text('data_out')->nullable();

                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->dateTime('created')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->dateTime('updated')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
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
        Schema::table('tbl_admin_api_log', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_admin_api_log', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_admin_api_log', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_admin_api_log');
    }
}
