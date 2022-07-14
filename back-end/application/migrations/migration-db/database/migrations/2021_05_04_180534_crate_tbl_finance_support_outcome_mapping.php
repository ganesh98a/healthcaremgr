<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrateTblFinanceSupportOutcomeMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_support_outcome_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('support_category_id')->nullable()->comment('primay key of tbl_finance_support_category');
            $table->unsignedTinyInteger('support_outcome_domain_id')->nullable()->comment('primay key of tbl_finance_support_outcome_domain');
            $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_support_outcome_mapping');
    }
}
