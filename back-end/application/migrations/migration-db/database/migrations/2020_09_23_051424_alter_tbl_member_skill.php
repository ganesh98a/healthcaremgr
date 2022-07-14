<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberSkill extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_skill', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_skill', 'skill_id')) {
                $table->unsignedInteger('skill_id')->nullable()->comment('tbl_references.id')->after('member_id');
                $table->foreign('skill_id')->references('id')->on('tbl_references')->onDelete(DB::raw('SET NULL'));
            }

            if (!Schema::hasColumn('tbl_member_skill', 'skill_level_id')) {
                $table->unsignedInteger('skill_level_id')->nullable()->comment('tbl_references.id')->after('skill_id');
                $table->foreign('skill_level_id')->references('id')->on('tbl_references')->onDelete(DB::raw('SET NULL'));
            }

            if (!Schema::hasColumn('tbl_member_skill', 'start_date')) {
                $table->dateTime('start_date')->nullable()->after('skill_level_id');
            }

            if (!Schema::hasColumn('tbl_member_skill', 'end_date')) {
                $table->dateTime('end_date')->nullable()->after('start_date');
            }

            if (!Schema::hasColumn('tbl_member_skill', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable()->after('created');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }

            if (!Schema::hasColumn('tbl_member_skill', 'updated')) {
                $table->dateTime('updated')->nullable()->after('created_by');
            }

            if (!Schema::hasColumn('tbl_member_skill', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable()->after('updated');
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_member_skill', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_skill', 'skill_id')) {
                $table->dropForeign(['skill_id']);
                $table->dropColumn('skill_id');
            }
            if (Schema::hasColumn('tbl_member_skill', 'skill_level_id')) {
                $table->dropForeign(['skill_level_id']);
                $table->dropColumn('skill_level_id');
            }
            if (Schema::hasColumn('tbl_member_skill', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('tbl_member_skill', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('tbl_member_skill', 'updated')) {
                $table->dropColumn('updated');
            }
            if (Schema::hasColumn('tbl_member_skill', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('tbl_member_skill', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
}
