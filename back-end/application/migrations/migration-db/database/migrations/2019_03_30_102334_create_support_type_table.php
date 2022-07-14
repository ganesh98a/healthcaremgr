<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupportTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_support_type')) {
            Schema::create('tbl_support_type', function (Blueprint $table) {
                $table->tinyIncrements('id');
                $table->string('name',50);
                $table->unsignedTinyInteger('status')->default(1)->comment('1 for active and 2 for inactive');
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
        Schema::dropIfExists('tbl_support_type');
    }
}
