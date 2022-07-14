<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRunUserDocCategoryListSeederForAddColumnTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_user_doc_category_list', function (Blueprint $table) {
           $seeder = new UserDocCategoryList();
           $seeder->run();
        });	
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_user_doc_category_list', function (Blueprint $table) {
            //
        });
    }
}
