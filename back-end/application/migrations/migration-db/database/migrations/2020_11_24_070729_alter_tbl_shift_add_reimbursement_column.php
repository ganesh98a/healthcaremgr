<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddReimbursementColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'scheduled_reimbursement')) {
                $table->decimal('scheduled_reimbursement', 10, 2)->nullable()->after('scheduled_travel');
            }
            if (!Schema::hasColumn('tbl_shift', 'actual_reimbursement')) {
                $table->decimal('actual_reimbursement', 10, 2)->nullable()->after('actual_travel');
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
            if (!Schema::hasColumn('tbl_shift', 'scheduled_reimbursement')) {
                $table->dropColumn('scheduled_reimbursement');
            }
            if (!Schema::hasColumn('tbl_shift', 'actual_reimbursement')) {
                $table->dropColumn('actual_reimbursement');
            }
        });
    }
}
