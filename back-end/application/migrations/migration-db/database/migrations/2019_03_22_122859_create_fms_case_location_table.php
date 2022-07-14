<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_location')) {
            Schema::create('tbl_fms_case_location', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('caseId')->index('caseId');
                    $table->string('address', 128);
                    $table->string('suburb', 100)->comment('city');
                    $table->unsignedTinyInteger('state');
                    $table->unsignedInteger('postal')->unsigned();
                    $table->string('lat', 100)->nullable();
                    $table->string('long', 100)->nullable();
                    $table->unsignedInteger('categoryId');
                });
                DB::statement('ALTER TABLE `tbl_fms_case_location` CHANGE `postal` `postal` int(4) unsigned zerofill NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_fms_case_location');
    }
}
