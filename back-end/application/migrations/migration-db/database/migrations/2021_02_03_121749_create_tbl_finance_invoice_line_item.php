<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinanceInvoiceLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_invoice_line_item')) {
            Schema::create('tbl_finance_invoice_line_item', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('invoice_id')->comment('tbl_finance_invoice.id');
                $table->foreign('invoice_id')->references('id')->on('tbl_finance_invoice')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('category_id')->comment('tbl_references.id');
                $table->foreign('category_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
                $table->decimal('units', 10, 2)->default('0.00');
                $table->decimal('unit_rate', 10, 2)->default('0.00');
                $table->decimal('total_cost', 10, 2)->default('0.00');
                $table->string('external_reference')->nullable();
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
        Schema::table('tbl_finance_invoice_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_invoice_line_item', 'invoice_id')) {
                $table->dropForeign(['invoice_id']);
            }
            if (Schema::hasColumn('tbl_finance_invoice_line_item', 'category_id')) {
                $table->dropForeign(['category_id']);
            }
            if (Schema::hasColumn('tbl_finance_invoice_line_item', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_finance_invoice_line_item', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_finance_invoice_line_item');
    }
}
