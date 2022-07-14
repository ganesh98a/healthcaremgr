<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftNdisItemsAddLineItemId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function listTableForeignKeys($table) {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }

    public function up()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_shift_ndis_line_item");
            $table->unsignedInteger('line_item_price_id')->nullable()->comment('tbl_finance_line_item_price.id')->change();
            $table->unsignedInteger('line_item_id')->comment('tbl_finance_line_item.id')->after('category');
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'line_item_id') && !in_array("tbl_shift_item_li_id", $list)) {
                $table->foreign('tbl_shift_item_li_id')->references('id')->on('tbl_finance_line_item');
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
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_shift_ndis_line_item");
            if (Schema::hasColumn('tbl_shift_ndis_line_item', 'line_item_id')) {
                if (Schema::hasColumn('tbl_shift_ndis_line_item', 'line_item_id') && in_array("tbl_shift_item_li_id", $list)) {
                    $table->dropForeign('tbl_shift_item_li_id');
                }
                $table->dropColumn('line_item_id');
            }
            $table->unsignedInteger('line_item_price_id')->nullable(false)->comment('tbl_finance_line_item.id')->change();
        });
    }
}
