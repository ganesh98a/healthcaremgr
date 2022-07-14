<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblReferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_references', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type')->comment('tbl_reference_data_type.id');
            $table->string('code',200);
            $table->string('display_name',200);
            $table->text('definition')->nullable();
            $table->string('parent_id',200)->nullable();
            $table->string('source',150)->nullable();
            $table->date('start_date')->default('0000-00-00');
            $table->date('end_date')->default('0000-00-00');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->dateTime('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedTinyInteger('archive')->comment('1- delete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_references');
    }
}
