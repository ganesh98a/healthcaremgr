<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsTemplateTableAddShowTitleOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sms_template', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sms_template', 'show_title_order')) {
                $table->unsignedInteger('show_title_order')->default('0')->comment('sms title order');
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
        Schema::table('tbl_sms_template', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sms_template', 'show_title_order')) {
                $table->dropColumn('show_title_order');
            }
        });
    }
}
