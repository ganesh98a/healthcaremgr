<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmStaffDisableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_staff_disable')) {
            Schema::create('tbl_crm_staff_disable', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_staff_id');
                $table->string('disable_account',30);
                $table->string('account_allocated',30);
                $table->unsignedInteger('account_allocated_to');
                $table->string('relevant_note',30);
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
        Schema::dropIfExists('tbl_crm_staff_disable');
    }
}
