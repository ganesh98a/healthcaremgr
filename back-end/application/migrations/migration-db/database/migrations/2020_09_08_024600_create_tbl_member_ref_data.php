<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberRefData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_ref_data')) {
            Schema::create('tbl_member_ref_data', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->comment('tbl_member.id');
                $table->foreign('member_id')->references('id')->on('tbl_member')->onDelete('CASCADE');

                $table->unsignedInteger('ref_id')->comment("tbl_references.id");
                $table->foreign('ref_id')->references('id')->on('tbl_references')->onDelete('CASCADE');
                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));

                $table->unsignedInteger('created_by');
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
        if (Schema::hasTable('tbl_member_ref_data')) {
            Schema::table('tbl_member_ref_data', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_member_ref_data', 'member_id')) {
                    $table->dropForeign(['member_id']);
                }
                if (Schema::hasColumn('tbl_member_ref_data', 'ref_id')) {
                    $table->dropForeign(['ref_id']);
                }
                if (Schema::hasColumn('tbl_member_ref_data', 'created_by')) {
                    $table->dropForeign(['created_by']);
                }
                if (Schema::hasColumn('tbl_member_ref_data', 'updated_by')) {
                    $table->dropForeign(['updated_by']);
                }
            });
        }
        Schema::dropIfExists('tbl_member_ref_data');
    }
}
