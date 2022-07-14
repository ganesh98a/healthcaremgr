<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftNdisLineItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_shift_ndis_line_item', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_id')->nullable()->comment('reference of tbl_shift.id');
            $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('line_item_id')->comment('reference of tbl_finance_line_item.id');
            $table->unsignedInteger('sa_line_item_id')->comment('reference of tbl_service_agreement_items.id');
            $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'shift_id')) {
                $table->dropForeign(['shift_id']);
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'line_item_id')) {
                $table->dropForeign(['line_item_id']);
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'sa_line_item_id')) {
                $table->dropForeign(['sa_line_item_id']);
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
        });
        Schema::dropIfExists('tbl_shift_ndis_line_item');
    }
}
