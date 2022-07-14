<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftOrgRequirementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_org_requirement')) {
        Schema::create('tbl_shift_org_requirement', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',150);
            $table->unsignedTinyInteger('archive');
            $table->timestamp('created')->useCurrent();
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
        Schema::dropIfExists('tbl_shift_org_requirement');
    }
}
