<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblShift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'owner_id')) {
                $table->dropForeign(['owner_id']);
            }

            if (!Schema::hasColumn('tbl_shift', 'cancel_reason_id')) {
                $table->unsignedInteger('cancel_reason_id')->nullable()->comment('tbl_references.id')->after('status');
                $table->foreign('cancel_reason_id')->references('id')->on('tbl_references')->onDelete(DB::raw('SET NULL'));
            }

            if (!Schema::hasColumn('tbl_shift', 'cancel_notes')) {
                $table->mediumtext('cancel_notes')->nullable()->after('cancel_reason_id');
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
            if (Schema::hasColumn('tbl_shift', 'cancel_reason_id')) {
                $table->dropForeign(['cancel_reason_id']);
                $table->dropColumn('cancel_reason_id');
            }
            if (Schema::hasColumn('tbl_shift', 'cancel_notes')) {
                $table->dropColumn('cancel_notes');
            }
        });
    }
}
