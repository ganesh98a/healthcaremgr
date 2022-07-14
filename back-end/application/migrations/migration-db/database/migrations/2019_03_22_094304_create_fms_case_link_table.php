<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_link')) {
            Schema::create('tbl_fms_case_link', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('caseId')->index('caseId');
                    $table->unsignedInteger('link_case');
                    $table->unsignedTinyInteger('archive')->default(0)->comment('1- Delete');
                    $table->dateTime('updated')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_fms_case_link');
    }
}
