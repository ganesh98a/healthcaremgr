<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation_member', 'status')) {
                $table->unsignedInteger('status')->nullable()->after('ref_no')->comment('tbl_references.id');
                $table->foreign('status')->references('id')->on('tbl_references')->onDelete(DB::raw('SET NULL'));
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
        Schema::table('tbl_organisation_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_member', 'status')) {
                $table->dropForeign(['status']);
                $table->dropColumn('status');
            }
        });
    }
}
