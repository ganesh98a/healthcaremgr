<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinanceInvoiceNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # rename the existing table to preserve it
        if (Schema::hasTable('tbl_finance_invoice')) {
            Schema::dropIfExists('tbl_finance_invoice_old');
            DB::statement("ALTER TABLE tbl_finance_invoice RENAME TO tbl_finance_invoice_old");
        }

        if (!Schema::hasTable('tbl_finance_invoice')) {
            Schema::create('tbl_finance_invoice', function (Blueprint $table) {
                $table->increments('id');
                $table->string('invoice_no', 200);
                $table->unsignedInteger('shift_id')->comment('tbl_shift.id');
                $table->foreign('shift_id')->references('id')->on('tbl_shift')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('account_type')->comment('1 = participant, 2 = org');
                $table->unsignedInteger('account_id')->comment('tbl_person.id or tbl_organization.id');
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
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_invoice', 'shift_id')) {
                $table->dropForeign(['shift_id']);
            }
            if (Schema::hasColumn('tbl_finance_invoice', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_finance_invoice', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_finance_invoice');
    }
}
