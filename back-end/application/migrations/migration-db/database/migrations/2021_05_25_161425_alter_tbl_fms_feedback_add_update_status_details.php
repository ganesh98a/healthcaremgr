<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsFeedbackAddUpdateStatusDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_fms_feedback', 'notes_reason')) {
                $table->longText('notes_reason')->nullable();
            }
            if (!Schema::hasColumn('tbl_fms_feedback', 'email_notification')) {
                $table->unsignedTinyInteger('email_notification')->default(0)->comment('0 - No, 1 - Yes');
            }
            if (!Schema::hasColumn('tbl_fms_feedback', 'notify_email')) {
                $table->string('notify_email')->nullable()->comment('send mail while update status');
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
        Schema::table('tbl_fms_feedback', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_feedback', 'notes_reason')) {
                $table->dropColumn('notes_reason');
            }
            if (Schema::hasColumn('tbl_fms_feedback', 'email_notification')) {
                $table->dropColumn('email_notification');
            }
            if (Schema::hasColumn('tbl_fms_feedback', 'notify_email')) {
                $table->dropColumn('notify_email');
            }
        });
    }
}
