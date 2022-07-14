<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_reason')) {
            Schema::create('tbl_fms_case_reason', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('caseId')->index('caseId');
                    $table->string('title', 228);
                    $table->text('description');
                    $table->unsignedInteger('created_by')->index('created_by');
                    $table->unsignedTinyInteger('created_type')->comment('1- Member, 2- Admin');
                    $table->dateTime('created');
                    $table->timestamp('updated')->useCurrent();
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
        Schema::dropIfExists('tbl_fms_case_reason');
    }
}
