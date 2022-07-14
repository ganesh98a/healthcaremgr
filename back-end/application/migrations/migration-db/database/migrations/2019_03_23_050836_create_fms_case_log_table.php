<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_log')) {
            Schema::create('tbl_fms_case_log', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('caseId')->index('caseId');
                    $table->string('title', 128);
                    $table->unsignedInteger('created_by')->nullable();
                    $table->unsignedTinyInteger('created_type')->comment('1- Member, 2- Admin ');
                    $table->dateTime('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_fms_case_log');
    }
}
