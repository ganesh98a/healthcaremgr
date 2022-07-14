<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAdminProcessEventAddSmsTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_admin_process_event', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_admin_process_event', 'sms_template')) {
                $table->bigInteger('sms_template')->unsigned()->nullable()->comment('tbl_sms_template.id')->after('email_template');
                $table->foreign('sms_template')->references('id')->on('tbl_sms_template')->onDelete('cascade');
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
        Schema::table('tbl_admin_process_event', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_admin_process_event', 'sms_template')) {
                $table->dropForeign(['sms_template']);
                $table->dropColumn('sms_template');
            }
        });
    }
}
