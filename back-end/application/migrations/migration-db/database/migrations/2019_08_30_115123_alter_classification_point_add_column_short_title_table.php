<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClassificationPointAddColumnShortTitleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_classification_point', function (Blueprint $table) {		
			if (Schema::hasTable('tbl_classification_point')) {
				Schema::table('tbl_classification_point', function (Blueprint $table) {
						if (!Schema::hasColumn('tbl_classification_point','short_title')) {
						  $table->string('short_title',100)->after('point_name');
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
    public function down()
    {
        Schema::table('tbl_classification_point', function (Blueprint $table) {
            if (Schema::hasTable('tbl_classification_point')) {
				Schema::table('tbl_classification_point', function (Blueprint $table) {
						if (Schema::hasColumn('tbl_classification_point','short_title')) {
						  $table->dropColumn('short_title');
					  }					  
				});
			}
        });
    }
}
