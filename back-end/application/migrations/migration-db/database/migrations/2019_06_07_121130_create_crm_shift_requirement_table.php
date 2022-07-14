<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmShiftRequirementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!Schema::hasTable('tbl_crm_shift_requirement')) {
        Schema::create('tbl_crm_shift_requirement', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('crmRosterId');
            $table->unsignedTinyInteger('requirement_type')->comment('1- Shift requirement,2- Support required,3- mobility');
            $table->unsignedInteger('requirementId');
            $table->string('other',50);
            $table->unsignedTinyInteger('archived')->comment('1- Yes, 0- No');
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
        Schema::dropIfExists('tbl_crm_shift_requirement');
    }
}
