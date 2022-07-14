<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberAsAddColumnIsNewMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
             $table->unsignedInteger('is_new_member')->default(0)->comment('0 old member, 1 =new member come from recruitment portal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_member')) {
            Schema::table('tbl_member', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_member','is_new_member')){
                    $table->dropColumn('is_new_member');
                } 
            });

        }
    }
}
