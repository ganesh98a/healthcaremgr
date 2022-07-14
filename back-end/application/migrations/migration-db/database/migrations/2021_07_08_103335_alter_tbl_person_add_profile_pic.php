<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddProfilePic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'profile_pic')) {
                $table->text('profile_pic')->nullable()->comment('Base64 data for contact profile pic');
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
            if (Schema::hasColumn('tbl_person', 'profile_pic')) {
                $table->dropColumn('profile_pic');
            }
        });
    }
}
