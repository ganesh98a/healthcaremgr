<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAccessLock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_access_lock')) {
            Schema::create('tbl_access_lock', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('object_type_id')->default('1')->comment('1 = shift');
                $table->unsignedInteger('object_id');
                $table->unsignedInteger('add_lock')->default('0');
                $table->unsignedInteger('edit_lock')->default('0');
                $table->unsignedInteger('delete_lock')->default('0');
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
        Schema::table('tbl_access_lock', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_access_lock', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_access_lock', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_access_lock');
    }
}
