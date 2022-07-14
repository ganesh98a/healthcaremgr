<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberAddProfilePic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
           if (!Schema::hasColumn('tbl_member', 'profile_pic')) {
                $table->text('profile_pic')->nullable()->comment('Base64 data for member profile pic');
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
            if (Schema::hasColumn('tbl_member', 'profile_pic')) {
                $table->dropColumn('profile_pic');
            }
        });
    }
}
