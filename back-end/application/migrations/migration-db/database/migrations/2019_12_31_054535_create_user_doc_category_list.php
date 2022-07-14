<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDocCategoryList extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_user_doc_category_list', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_type')->comment('1-site/2-participant/3-reserve in shift/4-org/5 - sub-org/6 - reserve in quote/7 - house');
            $table->string('title', 200)->nullable();
            $table->string('name', 200);
            $table->string('key_name', 200);
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->unsignedSmallInteger('archive')->default('0')->comment('1- Yes, 0- No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_user_doc_category_list');
    }

}
