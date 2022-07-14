<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityFieldHistoryAddSaTotalField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_opportunity_field_history')) {          
            \DB::statement("ALTER TABLE `tbl_opportunity_field_history` CHANGE `field` `field` 
                ENUM('line_item_sa_total', 'line_item_total', 'created','converted','topic','opportunity type','owner','status','source','amount','description') 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_opportunity_field_history', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity_field_history', 'field')) {
                $table->dropColumn('field');
            }
        });
    }
}
