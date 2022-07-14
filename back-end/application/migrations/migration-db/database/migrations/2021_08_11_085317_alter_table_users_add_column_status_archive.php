<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUsersAddColumnStatusArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_users', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_users', 'token')) {
                $table->text('token')->nullable()->after("user_type")->comment('jwt token');
            }
            if (!Schema::hasColumn('tbl_users', 'status')) {
                $table->unsignedInteger('status')->default(0)->after("password_token")->comment('0- inactive, 1-active');
            }
            if (!Schema::hasColumn('tbl_users', 'archive')) {
                $table->unsignedInteger('archive')->default(0)->after("status")->comment('0- inactive, 1-active');
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
        Schema::table('tbl_users', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_users', 'token')) {
                $table->dropColumn('token');
            }
            if (Schema::hasColumn('tbl_users', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('tbl_users', 'archive')) {
                $table->dropColumn('archive');
            }
        });        
    }
}
