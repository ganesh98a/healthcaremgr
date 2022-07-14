<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinanceTimesheet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_timesheet')) {
            Schema::create('tbl_finance_timesheet', function (Blueprint $table) {
                $table->increments('id');
                $table->string('timesheet_no', 200);
                $table->unsignedInteger('shift_id')->comment('tbl_shift.id');
                $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('member_id')->comment('tbl_member.id');
                $table->foreign('member_id')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->unsignedInteger('status')->default('0')->comment('0 draft, 1 submitted, 2 approved, 3 pending payment, 4 paid');
                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->dateTime('created')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->dateTime('updated')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_finance_timesheet', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_timesheet', 'shift_id')) {
                $table->dropForeign(['shift_id']);
            }
            if (Schema::hasColumn('tbl_finance_timesheet', 'member_id')) {
                $table->dropForeign(['member_id']);
            }
            
            if (Schema::hasColumn('tbl_finance_timesheet', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_finance_timesheet', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_finance_timesheet');
    }
}
