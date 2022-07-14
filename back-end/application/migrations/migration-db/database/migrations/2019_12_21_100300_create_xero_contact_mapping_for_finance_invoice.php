<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXeroContactMappingForFinanceInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tbl_participant_xero_contact_mapping');
        Schema::dropIfExists('tbl_organisation_site_xero_contact_mapping');
        
        if (!Schema::hasTable('tbl_xero_contact_mapping_for_finance_invoice')) {
            Schema::create('tbl_xero_contact_mapping_for_finance_invoice', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedSmallInteger('booked_by')->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote/7- house');
                $table->unsignedInteger('xero_for')->comment('tbl_participant/tbl_organisation/tbl_organisation_site/tbl_organisation_house');
                $table->string('xero_contact_id', 255);
                $table->smallInteger('archive')->comment('0 -Not/1 - Archive');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_xero_contact_mapping_for_finance_invoice');
    }
}
