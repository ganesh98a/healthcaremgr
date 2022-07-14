<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClassificationLevelUpdateLevelNameLengthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (Schema::hasTable('tbl_classification_level')) {
        Schema::table('tbl_classification_level', function (Blueprint $table) {
			 if (Schema::hasColumn('tbl_classification_level','level_name')) {
					$table->string('level_name', 200)->change();
			 }
			 if (Schema::hasColumn('tbl_classification_level','status')) {
					$table->unsignedSmallInteger('status')->default('1')->comment('1- Active, 0- Inactive')->change();
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
		if (Schema::hasTable('tbl_classification_level')) {
			 if (Schema::hasColumn('tbl_classification_level','level_name')) {
				Schema::table('tbl_classification_level', function (Blueprint $table) {
					$table->string('level_name', 50)->change();
				});
			 }
		}
    }
}
