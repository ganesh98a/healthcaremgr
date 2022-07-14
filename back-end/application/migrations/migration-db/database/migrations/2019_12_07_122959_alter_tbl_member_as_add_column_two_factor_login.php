<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberAsAddColumnTwoFactorLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_member','two_factor_login')){
                $table->unsignedSmallInteger('two_factor_login')->default('0')->comment('0 two factor is disable, 1 enable (used in marketing module)');
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
            if(Schema::hasColumn('tbl_member','two_factor_login')){
                $table->dropColumn('two_factor_login');
            }
        });
    }
}
