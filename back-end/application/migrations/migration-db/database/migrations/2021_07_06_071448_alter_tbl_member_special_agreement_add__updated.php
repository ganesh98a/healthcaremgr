<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberSpecialAgreementAddUpdated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_special_agreement', function (Blueprint $table) {

            if (!Schema::hasColumn('tbl_member_special_agreement', 'updated')) {
                $table->dateTime('updated')->nullable();
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
        Schema::table('tbl_member_special_agreement', function (Blueprint $table) {

            if (Schema::hasColumn('tbl_member_special_agreement', 'updated')) {
                $table->dropColumn('updated');
            }
        });
    }
}
