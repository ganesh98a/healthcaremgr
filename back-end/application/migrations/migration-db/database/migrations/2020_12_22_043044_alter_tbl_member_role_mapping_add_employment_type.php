<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberRoleMappingAddEmploymentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_role_mapping', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_role_mapping', 'employment_type')) {
                $table->unsignedInteger('employment_type')->nullable()->comment('reference of tbl_references.id')->after('level');
                $table->foreign('employment_type')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_member_role_mapping', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_role_mapping', 'employment_type')) {
                // Drop foreign key
                $table->dropForeign(['employment_type']);
                $table->dropColumn('employment_type');
            }
        });
    }
}
