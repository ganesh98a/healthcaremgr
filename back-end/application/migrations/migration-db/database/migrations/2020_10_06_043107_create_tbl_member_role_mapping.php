<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberRoleMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_role_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id')->nullable()->comment('tbl_member.id');
            $table->foreign('member_id')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('member_role_id')->nullable()->comment('tbl_member_role.id');
            $table->foreign('member_role_id')->references('id')->on('tbl_member_role')->onDelete('cascade');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->unsignedInteger('pay_point')->nullable();
            $table->unsignedInteger('level')->nullable();
            $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
            $table->dateTime('created')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('updated')->nullable();
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
        Schema::table('tbl_member_role_mapping', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_role_mapping', 'member_id')) {
                $table->dropForeign(['member_id']);
            }
            if (Schema::hasColumn('tbl_member_role_mapping', 'member_role_id')) {
                $table->dropForeign(['member_role_id']);
            }
            if (Schema::hasColumn('tbl_member_role_mapping', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_member_role_mapping', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_member_role_mapping');
    }
}
