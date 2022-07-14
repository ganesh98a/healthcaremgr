<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeAdminPortalUsernamesToPrimaryEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table("tbl_member", function(Blueprint $table) {
            $TBL_MEMBER_EMAIL_EMAIL_COL_LENGTH = 64;

            // increase column length from 20 to 64 because tbl_member_email.email column has length of 64
            if (Schema::hasColumn("tbl_member", "username")) {
                $table->string("username", $TBL_MEMBER_EMAIL_EMAIL_COL_LENGTH)->change(); 
            }

            // create nullable backup column. Let's not force coders to fill this column, so make it nullable
            if ( ! Schema::hasColumn('tbl_member', 'username_bk_29042020')) {
                $table->string("username_bk_29042020", $TBL_MEMBER_EMAIL_EMAIL_COL_LENGTH)->nullable()->comment('Backup username (29 Apr 2020)');
            }
        });


        // Backup username only if not blank
        DB::statement("UPDATE `tbl_member` SET `username_bk_29042020` = `username` WHERE `username` != ''");

        // WARNING: Critical code
        // Use tbl_member_email.email as new username if username was backed up successfully (by the SQL above)
        DB::statement("UPDATE tbl_member 
            INNER JOIN tbl_member_email ON tbl_member_email.primary_email = 1 AND tbl_member_email.memberId = tbl_member.id
            SET tbl_member.username = tbl_member_email.email
            WHERE tbl_member.username_bk_29042020 IS NOT NULL
            AND tbl_member.username_bk_29042020 != ''
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("tbl_member", function(Blueprint $table) {

            // restore username from backup
            // if there are new users, those will not be affected because new user's username will never backed up
            DB::statement("UPDATE `tbl_member` SET `username` = `username_bk_29042020` WHERE `username_bk_29042020` IS NOT NULL OR `username_bk_29042020` != ''");

            // drop backup column
            if (Schema::hasColumn('tbl_member', 'username_bk_29042020')) {
                $table->dropColumn("username_bk_29042020");
            }

            // Should we revert the size of the column from 64 to 20? 
            // Nope, because username column will be filled with emails, which could contain more than 20 chars
        });
    }
}
