<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblClassificationPointAsUpdateSeederfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_classification_point', function (Blueprint $table) {
          $seeder = new ClassificationPointSeeder();
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
        Schema::table('tbl_classification_point', function (Blueprint $table) {
            //
        });
    }
}
