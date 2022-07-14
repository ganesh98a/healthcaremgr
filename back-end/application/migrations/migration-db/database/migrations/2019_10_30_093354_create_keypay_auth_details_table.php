<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeypayAuthDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_keypay_auth_details')) {
            Schema::create('tbl_keypay_auth_details', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('companyId')->unsigned()->comment('auto increment id of tbl_company table');
                $table->string('api_key',255)->nullable()->comment('keypay api key');
                $table->string('business_id',255)->nullable()->comment('keypay business_id');
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
        Schema::dropIfExists('tbl_keypay_auth_details');
    }
}
