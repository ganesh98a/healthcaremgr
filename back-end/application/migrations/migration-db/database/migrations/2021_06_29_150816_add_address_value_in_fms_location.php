<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddressValueInFmsLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_feedback_location', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_fms_feedback_location', 'address_id')) {
                $table->unsignedInteger('address_id')->default('0')->after("address")->comments('id for participant address');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_fms_feedback_location', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_feedback_location', 'address_id')) {
                $table->dropColumn('address_id');
            }
        });
    }
}
