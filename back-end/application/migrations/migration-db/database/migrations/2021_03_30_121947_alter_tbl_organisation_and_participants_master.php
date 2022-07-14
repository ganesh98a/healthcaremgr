<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationAndParticipantsMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'cost_book_id')) {
                $table->unsignedInteger('cost_book_id')->nullable()->after("is_site")->comment('tbl_references.id');
                $table->foreign('cost_book_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
            }
        });

        Schema::table('tbl_participants_master', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_participants_master', 'cost_book_id')) {
                $table->unsignedInteger('cost_book_id')->nullable()->after("role_id")->comment('tbl_references.id');
                $table->foreign('cost_book_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation', 'cost_book_id')) {
                $table->dropForeign(['cost_book_id']);
                $table->dropColumn('cost_book_id');
            }
        });

        Schema::table('tbl_participants_master', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participants_master', 'cost_book_id')) {
                $table->dropForeign(['cost_book_id']);
                $table->dropColumn('cost_book_id');
            }
        });
    }
}
