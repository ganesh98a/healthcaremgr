<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceQuoteEnquiryCustomerChangePostcode extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_quote_enquiry_customer', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote_enquiry_customer', 'poscode')) {
                $table->renameColumn('poscode', 'postcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_quote_enquiry_customer', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote_enquiry_customer', 'postcode')) {
                $table->renameColumn('postcode', 'poscode');
            }
        });
    }

}
