<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_house_requirements')) {
            Schema::create('tbl_house_requirements', function(Blueprint $table)
                {
                    $table->unsignedInteger('houseId')->index('houseId');
                    $table->unsignedInteger('requirementId')->index('requirementId');
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
        Schema::dropIfExists('tbl_house_requirements');
    }
}
