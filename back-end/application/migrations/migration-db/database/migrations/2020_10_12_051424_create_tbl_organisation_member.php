<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblOrganisationMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # rename the existing table to preserve it
        if (Schema::hasTable('tbl_organisation_member')) {
            Schema::dropIfExists('tbl_organisation_member_old');
            DB::statement("ALTER TABLE tbl_organisation_member RENAME TO tbl_organisation_member_old");
        }

        if (!Schema::hasTable('tbl_organisation_member')) {
            Schema::create('tbl_organisation_member', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('organisation_id')->comment('tbl_organisation.id');
                $table->foreign('organisation_id')->references('id')->on('tbl_organisation')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('member_id')->comment('tbl_member.id');
                $table->foreign('member_id')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->dateTime('reg_date')->nullable();
                $table->string('ref_no', 200)->nullable();
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
        Schema::table('tbl_organisation_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_member', 'organisation_id')) {
                $table->dropForeign(['organisation_id']);
            }
            if (Schema::hasColumn('tbl_organisation_member', 'member_id')) {
                $table->dropForeign(['member_id']);
            }
            if (Schema::hasColumn('tbl_organisation_member', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_organisation_member', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_organisation_member');
    }
}
