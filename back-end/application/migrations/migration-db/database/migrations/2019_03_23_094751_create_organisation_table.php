<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_organisation')) {
            Schema::create('tbl_organisation', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('companyId')->index('companyId');
                    $table->string('name', 128)->index('name');
                    $table->string('abn', 20);
                    $table->string('logo_file', 64);
                    $table->unsignedSmallInteger('parent_org')->default(0);
                    $table->string('website', 228);
                    $table->unsignedTinyInteger('payroll_tax');
                    $table->unsignedTinyInteger('gst')->default('0')->comment('1- Yes, 0- No');
                    $table->unsignedTinyInteger('enable_portal_access')->default('0')->comment('1- Enable, 0- Disable');
                    $table->unsignedTinyInteger('status')->default(1)->comment('1- Yes, 0- No');
                    $table->unsignedTinyInteger('booking_status')->default('0')->comment('1- Yes, 0- No');
                    $table->date('booking_date');
                    $table->unsignedTinyInteger('archive')->default('0')->comment('1- Yes, 0- No');
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
        Schema::dropIfExists('tbl_organisation');
    }
}
