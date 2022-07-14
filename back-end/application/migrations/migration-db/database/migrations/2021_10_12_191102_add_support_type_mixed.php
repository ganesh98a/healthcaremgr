<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupportTypeMixed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_support_type', function (Blueprint $table) {
            $object = array("type" => "Mixed", "key_name" => "mixed",  "created_at" => "2021-10-12 01:40:12", "archive" => 0);
            DB::table('tbl_finance_support_type')->updateOrInsert(['type' => $object['type'],'key_name' => $object['key_name']],$object);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_support_type', function (Blueprint $table) {
            DB::statement("UPDATE `tbl_finance_support_type` SET `archive` = '1' WHERE `key_name` = 'mixed'");
        });
    }
}
