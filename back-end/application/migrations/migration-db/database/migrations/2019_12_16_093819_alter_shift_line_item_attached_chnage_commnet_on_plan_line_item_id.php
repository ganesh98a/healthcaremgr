<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftLineItemAttachedChnageCommnetOnPlanLineItemId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift_line_item_attached', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_line_item_attached', 'plan_line_itemId')) {
                $table->unsignedInteger('plan_line_itemId')->comment('primary key tbl_user_plan_line_item')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_shift_line_item_attached', function (Blueprint $table) {
            
        });
    }

}
