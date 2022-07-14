<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblNotificationUserTypeCommentChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_notification', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_notification', 'user_type')) {

                $table->smallInteger('user_type')->comment('1- Member, 2- Participant, 3-Site, 4-House, 5- Applicant')->change();

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
        Schema::dropIfExists('tbl_notification');
    }
}
