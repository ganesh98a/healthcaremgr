<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRosterShiftLocation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_roster_shift_location')) {
            Schema::create('tbl_roster_shift_location', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('roster_shiftId')->index()->comment('tbl_roster_shift auto incremant id');
                $table->string('address', 128);
                $table->string('suburb', 100)->comment('city');
                $table->unsignedInteger('state');
                $table->string('postal', 10);
                $table->string('lat', 50)->nullable()->change();
                $table->string('long', 50)->nullable()->change();
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->smallInteger('archive')->default(0)->comment('0 -Not/1 - Archive');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_roster_shift_location');
    }

}
