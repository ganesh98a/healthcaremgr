<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSiteDocsRenameTableAndUserType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_site_docs', function (Blueprint $table) {
            if (Schema::hasTable('tbl_site_docs') && !Schema::hasTable('tbl_house_and_site_docs')) {
                Schema::rename("tbl_site_docs", "tbl_house_and_site_docs");
            }
        });

        Schema::table('tbl_house_and_site_docs', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_house_and_site_docs', 'user_type')) {
                $table->unsignedInteger('user_type')->comment('1 - site/2 - house')->default('1')->after('id');
            }
            if (!Schema::hasColumn('tbl_house_and_site_docs', 'doc_category')) {
                $table->unsignedInteger('doc_category')->comment('primary key tbl_user_doc_category_list')->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_house_and_site_docs', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_house_and_site_docs', 'user_type')) {
                $table->dropColumn('user_type');
            }

            if (Schema::hasColumn('tbl_house_and_site_docs', 'doc_category')) {
                $table->dropColumn('doc_category');
            }
        });

        Schema::table('tbl_site_docs', function (Blueprint $table) {
            if (!Schema::hasTable('tbl_site_docs') && Schema::hasTable('tbl_house_and_site_docs')) {
                Schema::rename("tbl_house_and_site_docs", "tbl_site_docs");
            }
        });
    }

}
