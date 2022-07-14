<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberAddPrevName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'previous_name')) {
                $table->string('previous_name',255)->nullable()->comment('previous_name data for contact')->after('lastname');
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
        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'previous_name')) {
                $table->dropColumn('previous_name');
            }
        });
    }
}
