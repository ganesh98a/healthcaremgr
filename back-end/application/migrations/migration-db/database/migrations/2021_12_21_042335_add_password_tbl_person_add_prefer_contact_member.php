<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPasswordTblPersonAddpreferContactMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       

        Schema::table('tbl_member', function (Blueprint $table) {
             if (!Schema::hasColumn('tbl_member', 'prefer_contact')) {
                 $table->string('prefer_contact', 6);
             }
         }); 
         Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'password')) {
                $table->string('password',255)->nullable();
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
        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'password')) {
                 $table->dropColumn('password');
            }
        });

        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'prefer_contact')) {
                 $table->dropColumn('prefer_contact');
            }
        });
    }
}
