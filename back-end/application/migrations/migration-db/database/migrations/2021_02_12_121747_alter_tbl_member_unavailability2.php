<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberUnavailability2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_unavailability', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member_unavailability', 'keypay_ref_id')) {
                $table->unsignedInteger('keypay_ref_id')->nullable()->after("type_id");
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
        Schema::table('tbl_member_unavailability', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_unavailability', 'keypay_ref_id')) {
                $table->dropColumn('keypay_ref_id');
            }
        });
    }
}
