<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantShiftTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_crm_participant_shift')) {
            Schema::create('tbl_crm_participant_shift', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crmRosterId');
                $table->unsignedTinyInteger('day')->comment('1- Mon, 2- Tue, 3- Wed, 4- Thu, 5- Fri,6- Sat,7- Sun');
                $table->string('shift_type', 15)->comment('1- Am, 2- Pm,3- Sleep-over,4- Atlive night');
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
    public function down() {
        Schema::dropIfExists('tbl_crm_participant_shift');
    }

}
