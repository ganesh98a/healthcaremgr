<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTblLeadsAddReferrersDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_leads', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_leads', 'referrer_firstname')) {
                $table->string('referrer_firstname', 50)->nullable();
            }
            if (!Schema::hasColumn('tbl_leads', 'referrer_lastname')) {
                $table->string('referrer_lastname', 50)->nullable();
            }
            if (!Schema::hasColumn('tbl_leads', 'referrer_email')) {
                $table->string('referrer_email', 50)->nullable();
            }
            if (!Schema::hasColumn('tbl_leads', 'referrer_phone')) {
                $table->string('referrer_phone', 50)->nullable();
            }
            if (!Schema::hasColumn('tbl_leads', 'referrer_relation')) {
                $table->string('referrer_relation', 50)->nullable()->comment('Relationship to Participant');
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
        Schema::table('tbl_leads', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_leads', 'referrer_firstname')) {
                $table->dropColumn('referrer_firstname');
            }
            if (Schema::hasColumn('tbl_leads', 'referrer_lastname')) {
                $table->dropColumn('referrer_lastname');
            }
            if (Schema::hasColumn('tbl_leads', 'referrer_email')) {
                $table->dropColumn('referrer_email');
            }
            if (Schema::hasColumn('tbl_leads', 'referrer_phone')) {
                $table->dropColumn('referrer_phone');
            }
            if (Schema::hasColumn('tbl_leads', 'referrer_relation')) {
                $table->dropColumn('referrer_relation');
            }
        });
    }
}
