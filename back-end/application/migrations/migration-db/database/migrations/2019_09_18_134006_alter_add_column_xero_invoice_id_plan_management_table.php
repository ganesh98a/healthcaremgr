<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnXeroInvoiceIdPlanManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_plan_management')) {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
          if(!Schema::hasColumn('tbl_plan_management','xero_invoice_id')){
              $table->string('xero_invoice_id',50);
          }
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
      if (Schema::hasTable('tbl_plan_management')) {
          Schema::table('tbl_plan_management', function (Blueprint $table) {
              if(Schema::hasColumn('tbl_plan_management','xero_invoice_id')){
                  $table->dropColumn('xero_invoice_id');
              }
          });

      }
    }
}
