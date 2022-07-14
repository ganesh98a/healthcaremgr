<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberContactAddUpdated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_contact', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_contact', 'updated')) {
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
        Schema::table('tbl_member_contact', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_contact', 'updated')) {
                $table->dropColumn('updated');
            }
        });
    }
}
