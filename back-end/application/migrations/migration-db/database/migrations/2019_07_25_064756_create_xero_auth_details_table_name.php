<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXeroAuthDetailsTableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_xero_auth_details')) {
            Schema::create('tbl_xero_auth_details', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('companyId')->unsigned()->comment('auto increment id of tbl_company table');
                $table->string('xero_consumer_key',255)->nullable()->comment('xero consumer key');
                $table->string('xero_consumer_secret',255)->nullable()->comment('xero consumer secret key');
                $table->string('xero_rsa_private_key',255)->nullable()->comment('xero private key file name');
                $table->smallInteger('status')->unsigned()->default('1')->comment('1-active and 0- inactive');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_xero_auth_details');
    }
}
