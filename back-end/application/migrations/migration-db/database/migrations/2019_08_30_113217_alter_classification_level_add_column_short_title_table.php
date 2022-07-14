<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClassificationLevelAddColumnShortTitleTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_classification_level', function (Blueprint $table) {
            if (Schema::hasTable('tbl_classification_level')) {
                Schema::table('tbl_classification_level', function (Blueprint $table) {
                    if (!Schema::hasColumn('tbl_classification_level', 'short_title')) {
                        $table->string('short_title', 100)->after('level_name');
                    }
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_classification_level', function (Blueprint $table) {
            if (Schema::hasTable('tbl_classification_level')) {
                Schema::table('tbl_classification_level', function (Blueprint $table) {
                    if (Schema::hasColumn('tbl_classification_level', 'short_title')) {
                        $table->dropColumn('short_title');
                    }
                });
            }
        });
    }

}
