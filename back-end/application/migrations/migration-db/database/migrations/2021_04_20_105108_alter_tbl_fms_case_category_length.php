<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsCaseCategoryLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_case_category', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_case_category', 'categoryId')) {

                $table->unsignedInteger('categoryId')->nullable()->comment('Reference id of the Feed category')->change();
            }
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
