<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNotificationAsUpdateCommentForUserType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_notification', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_notification','user_type')) {
                $table->unsignedSmallInteger('user_type')->unsigned()->comment('1- Member, 2- Participant, 3-site, 4-house')->change();
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
        Schema::table('tbl_notification', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_notification','user_type')) {
                $table->unsignedSmallInteger('user_type')->unsigned()->comment('1- Member, 2- Participant')->change();
            }
        });
    }
}
