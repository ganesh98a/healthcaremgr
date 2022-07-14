<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseAllCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_all_category')) {
            Schema::create('tbl_fms_case_all_category', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->string('name', 150);
                    $table->unsignedTinyInteger('archive')->default(0)->comment('0 for not archive and 1 for archive(delete)');
                    $table->dateTime('created');
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
        Schema::dropIfExists('tbl_fms_case_all_category');
    }
}
