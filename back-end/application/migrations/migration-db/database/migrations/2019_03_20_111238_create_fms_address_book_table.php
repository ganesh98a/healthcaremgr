<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsAddressBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_address_book')) {
            Schema::create('tbl_fms_address_book', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('companyId');
                $table->unsignedInteger('caseId');
                $table->unsignedTinyInteger('type')->comment('1- Member, 2- Participant');
                $table->unsignedInteger('ocs_id')->comment('Member or Participant id');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_fms_address_book');
    }
}
