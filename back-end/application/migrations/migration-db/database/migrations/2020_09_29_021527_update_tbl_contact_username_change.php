<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblContactUsernameChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'username')) {
                $table->string('username',255)->nullable()->after('lastname');
            }
            if (!Schema::hasColumn('tbl_person', 'password')) {
                $table->string('password',255)->nullable()->after('username');
            }
        });

        # update the username with primary email address and assign a temporary password
        # do it only for those contacts who are applicants
        DB::statement("UPDATE `tbl_person` SET `username` = NULL, password = NULL");

        $res = DB::select("select p.id, pe.email from tbl_recruitment_applicant ra, tbl_person p, tbl_person_email pe where ra.person_id = p.id and p.id = pe.person_id and p.username is null and pe.primary_email = 1 and pe.archive = 0");
        if($res) {
            $password_hash = password_hash("123456", PASSWORD_BCRYPT);
            foreach($res as $row) {
                $email = $row->email;
                DB::statement("UPDATE `tbl_person` SET `username` = '{$email}', password = '{$password_hash}' where id = {$row->id}");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('tbl_person', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
}
