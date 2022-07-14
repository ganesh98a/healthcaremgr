<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinanceTimesheetQuery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_timesheet_query')) {
            Schema::create('tbl_finance_timesheet_query', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('timesheet_id')->comment('tbl_finance_timesheet.id');
                $table->foreign('timesheet_id')->references('id')->on('tbl_finance_timesheet')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('category_id')->comment('tbl_references.id');
                $table->foreign('category_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_finance_timesheet_query', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_timesheet_query', 'timesheet_id')) {
                $table->dropForeign(['timesheet_id']);
            }
            if (Schema::hasColumn('tbl_finance_timesheet_query', 'category_id')) {
                $table->dropForeign(['category_id']);
            }
            if (Schema::hasColumn('tbl_finance_timesheet_query', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_finance_timesheet_query', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_finance_timesheet_query');
    }
}
