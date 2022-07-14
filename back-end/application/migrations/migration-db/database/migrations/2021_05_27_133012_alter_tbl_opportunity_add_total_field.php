<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityAddTotalField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_opportunity')) {
            Schema::table('tbl_opportunity', function (Blueprint $table) {
                $table->double('line_item_sa_total', 10, 2)->nullable()->comment('Line item SA total value')->after('amount');
                $table->double('line_item_total', 10, 2)->nullable()->comment('Line item total value')->after('line_item_sa_total');
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
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity', 'line_item_sa_total')) {
                $table->dropColumn('line_item_sa_total');
            }
            if (Schema::hasColumn('tbl_opportunity', 'line_item_total')) {
                $table->dropColumn('line_item_total');
            }
        });
    }
}
