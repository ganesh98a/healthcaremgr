<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftOrgRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_org_requirements')) {
        Schema::create('tbl_shift_org_requirements', function (Blueprint $table) {
                $table->unsignedInteger('shiftId')->index();
                $table->unsignedInteger('requirementId')->index();
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
        Schema::dropIfExists('tbl_shift_org_requirements');
    }
}
