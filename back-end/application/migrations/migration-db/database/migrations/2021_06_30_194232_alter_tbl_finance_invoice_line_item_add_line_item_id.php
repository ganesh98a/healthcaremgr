<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceInvoiceLineItemAddLineItemId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_invoice_line_item', 'line_item_id')) {
                $table->unsignedInteger('line_item_id')->nullable()->comment('reference of tbl_finance_line_item.id')->after('category_id');
                $table->foreign('line_item_id', 'tbl_f_li_li_fk')->references('id')->on('tbl_finance_line_item');
            }
            if (Schema::hasColumn('tbl_finance_invoice_line_item', 'category_id')) {
                $table->unsignedInteger('category_id')->comment('tbl_references.id')->nullable()->change();
            }
        });

        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_invoice', 'site_id')) {
                $table->unsignedInteger('site_id')->nullable()->comment('tbl_organization.id')->after("account_id");
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
        Schema::table('tbl_finance_invoice_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_invoice_line_item', 'line_item_id')) {
                $table->dropForeign('tbl_f_li_li_fk');
                $table->dropColumn('line_item_id');
            }
            if (Schema::hasColumn('tbl_finance_invoice_line_item', 'category_id')) {
                $table->unsignedInteger('category_id')->comment('tbl_references.id')->nullable(false)->change();
            }
        });
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_invoice', 'site_id')) {
                $table->dropColumn('site_id');
            }
        });
    }
}
