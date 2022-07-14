<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRosterShiftRequirement extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_roster_shift_requirement', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('roster_shiftId')->comment('primary key of tbl_roster_shift');
            $table->unsignedInteger('requirementId')->comment('primary key of tbl_participant_genral');
            $table->string('requirement_other', 255)->comment('if other checked then other requirement');
            $table->unsignedInteger('requirement_type')->comment('1 - mobility/2 - asistance');
            $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_roster_shift_requirement');
    }

}
