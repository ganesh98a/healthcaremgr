<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFmsCaseDepartment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_fms_case_department', function (Blueprint $table) {
            $table->unsignedInteger('case_id')->index('case_id');
            $table->foreign('case_id')->references('id')->on('tbl_fms_case')
                ->onUpdate('cascade')->onDelete('cascade')->comment('Case id from tbl_fms_case');
            $table->unsignedInteger('department_id')->comment('Department ID from reference data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
