<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMemberAddColumnIsSuperAdmin extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasTable('tbl_member') && !Schema::hasColumn('tbl_member', 'is_super_admin')) {
                $table->unsignedSmallInteger('is_super_admin')->default(0)->comment('1 - Yes/0 - No')->after("position");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasTable('tbl_member') && Schema::hasColumn('tbl_member', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });
    }

}
