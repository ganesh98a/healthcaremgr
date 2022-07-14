<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblTblMemberUpdateUserTypeComment extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        if (Schema::hasTable('tbl_member')) {
            Schema::table('tbl_member', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_member', 'user_type')) {
                    $table->unsignedInteger('user_type')->comment("primary key tbl_admin_user_type")->change();
                }
            });
            
            DB::unprepared("UPDATE `tbl_member` SET `user_type` = '1' WHERE `tbl_member`.`id` > 0 and user_type = 0");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_member', function (Blueprint $table) {
            //
        });
    }

}
