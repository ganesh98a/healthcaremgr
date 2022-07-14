<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblSalesRelationAddCanBook extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sales_relation', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_sales_relation', 'can_book')) {
                $table->unsignedTinyInteger('can_book')->nullable()->comment("no=0, yes=1. Null can also mean no");
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
        Schema::table('tbl_sales_relation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_relation', 'can_book')) {
                $table->dropColumn('can_book');
            }
        });
    }
}
