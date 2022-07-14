<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddTravelDurationAndDistance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            $table->decimal('scheduled_travel_duration')->nullable()->comment('scheduled commuting travel duration')->after('scheduled_travel');
            $table->decimal('scheduled_travel_distance')->nullable()->comment('scheduled commuting travel distance')->after('scheduled_travel_duration');
            $table->decimal('actual_travel_duration')->nullable()->comment('actual commuting travel duration')->after('actual_travel');
            $table->decimal('actual_travel_distance')->nullable()->comment('actual commuting travel distance')->after('actual_travel_duration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'scheduled_travel_duration')) {
                $table->dropColumn('scheduled_travel_duration');
            }
            if (Schema::hasColumn('tbl_shift', 'scheduled_travel_distance')) {
                $table->dropColumn('scheduled_travel_distance');
            }
            if (Schema::hasColumn('tbl_shift', 'actual_travel_duration')) {
                $table->dropColumn('actual_travel_duration');
            }
            if (Schema::hasColumn('tbl_shift', 'actual_travel_distance')) {
                $table->dropColumn('actual_travel_distance');
            }
        });
    }
}
