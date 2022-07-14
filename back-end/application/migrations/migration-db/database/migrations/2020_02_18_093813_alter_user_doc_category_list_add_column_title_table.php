<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserDocCategoryListAddColumnTitleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_user_doc_category_list')){
            Schema::table('tbl_user_doc_category_list', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_user_doc_category_list','title')){
                    $table->string('title',200)->nullable()->after('user_type');
                }
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
         if(Schema::hasTable('tbl_user_doc_category_list')){
            Schema::table('tbl_user_doc_category_list', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_user_doc_category_list','title')){
                    $table->dropColumn('title');
                }
            });
        }
    }
}
