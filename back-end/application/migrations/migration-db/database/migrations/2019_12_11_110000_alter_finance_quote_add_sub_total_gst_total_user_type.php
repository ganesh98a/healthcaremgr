<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceQuoteAddSubTotalGstTotalUserType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_quote', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_quote', 'sub_total')) {
                $table->double('sub_total', 10, 2)->comment('total of all items include manual item')->after('pdf_file');
            }
            if (!Schema::hasColumn('tbl_finance_quote', 'gst')) {
                $table->double('gst', 10, 2)->after('sub_total');
            }
            if (!Schema::hasColumn('tbl_finance_quote', 'total')) {
                $table->double('total', 10, 2)->after('gst')->comment('subtotal + gst + other charegs');
            }
            if (Schema::hasColumn('tbl_finance_quote', 'archive')) {
                $table->dropColumn('archive');
            }
        });

        Schema::table('tbl_finance_quote', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote', 'user_type')) {
                $table->unsignedSmallInteger('user_type')->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - Other(new customer)')->nullable()->change();
            }
        });

        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'booked_by')) {
                $table->unsignedSmallInteger('booked_by')->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote')->nullable()->change();
            }
        });

        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_invoice', 'booked_by')) {
                $table->unsignedSmallInteger('booked_by')->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote')->nullable()->change();
            }
        });
        Schema::table('tbl_finance_statement', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_statement', 'booked_by')) {
                $table->unsignedSmallInteger('booked_by')->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_quote', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote', 'sub_total')) {
                $table->dropColumn('sub_total');
            }
            if (Schema::hasColumn('tbl_finance_quote', 'gst')) {
                $table->dropColumn('gst');
            }
            if (Schema::hasColumn('tbl_finance_quote', 'total')) {
                $table->dropColumn('total');
            }
        });
    }

}
