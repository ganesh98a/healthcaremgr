<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunUserDocCategoryListSeederInitital extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_user_doc_category_list', function (Blueprint $table) {
            $obj = new UserDocCategoryList();
            $obj->run();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_user_doc_category_list', function (Blueprint $table) {
            //
        });
    }

}
