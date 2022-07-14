<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddContactInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'email')) {
                $table->string('email', 255)->nullable()->after("person_id");
            }

            if (!Schema::hasColumn('tbl_shift', 'phone')) {
                $table->string('phone', 20)->nullable()->after("email");
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
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'email')) {
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('tbl_shift', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
}
