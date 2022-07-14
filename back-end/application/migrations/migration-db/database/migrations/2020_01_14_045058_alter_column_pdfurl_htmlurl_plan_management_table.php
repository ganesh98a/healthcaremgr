<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnPdfurlHtmlurlPlanManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
          DB::unprepared("ALTER TABLE `tbl_plan_management`
              CHANGE `pdf_url` `pdf_url` LONGTEXT NOT NULL  AFTER `updated`,
              CHANGE `html_url` `html_url` LONGTEXT NOT NULL AFTER `pdf_url`");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
            //
        });
    }
}
