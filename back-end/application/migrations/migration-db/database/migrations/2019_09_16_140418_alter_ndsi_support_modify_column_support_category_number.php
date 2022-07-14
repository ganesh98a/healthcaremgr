<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNdsiSupportModifyColumnSupportCategoryNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_ndis_support', function (Blueprint $table) {
            $table->renameColumn('supoort_category_number', 'support_category_number');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_ndis_support', function (Blueprint $table) {
            $table->renameColumn('support_category_number', 'supoort_category_number');   
        });
    }
}


