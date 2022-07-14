<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAccessRoleObject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_access_role_object')) {
            Schema::create('tbl_access_role_object', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('access_role_id')->comment('tbl_access_role.id');
                $table->foreign('access_role_id')->references('id')->on('tbl_access_role')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('module_object_id')->comment('tbl_module_object.id');
                $table->foreign('module_object_id')->references('id')->on('tbl_module_object')->onUpdate('cascade')->onDelete('cascade');
                $table->text('name');
                $table->unsignedInteger('read_access')->default('0')->comment('0 = no, 1 = yes');
                $table->unsignedInteger('create_access')->default('0')->comment('0 = no, 1 = yes');
                $table->unsignedInteger('edit_access')->default('0')->comment('0 = no, 1 = yes');
                $table->unsignedInteger('delete_access')->default('0')->comment('0 = no, 1 = yes');
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
        Schema::table('tbl_access_role_object', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_access_role_object', 'access_role_id')) {
                $table->dropForeign(['access_role_id']);
            }
            if (Schema::hasColumn('tbl_access_role_object', 'module_object_id')) {
                $table->dropForeign(['module_object_id']);
            }
            if (Schema::hasColumn('tbl_access_role_object', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_access_role_object', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_access_role_object');
    }
}
