<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddressTblMemberAddressTblPersonColumnSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'username')) {
                $table->string('username',255)->change();
            }
        });
        Schema::table('tbl_member_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_address', 'street')) {
                $table->string('street',255)->change();
            }
        });
        Schema::table('tbl_person_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person_address', 'street')) {
                $table->string('street',255)->change();
            }
        });
    }
  
    
}
