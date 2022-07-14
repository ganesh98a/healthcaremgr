<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClassificationPointAsAddArchiveColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_classification_point', function (Blueprint $table) {
           if (!Schema::hasColumn('tbl_classification_point', 'archive')) {
                $table->unsignedInteger('archive')->comment('1 - Yes/ 0 - Not');
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
        Schema::table('tbl_classification_point', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_classification_point', 'archive')) {
                $table->dropColumn('archive');
            }
        });
    }
}
