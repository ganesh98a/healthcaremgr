<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceFundingTypeRenameTableToFundingType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_funding_type', function (Blueprint $table) {
            if (Schema::hasTable('tbl_finance_funding_type') && !Schema::hasTable('tbl_funding_type')) {
                Schema::rename('tbl_finance_funding_type', 'tbl_funding_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_funding_type', function (Blueprint $table) {
            if (!Schema::hasTable('tbl_finance_funding_type') && Schema::hasTable('tbl_funding_type')) {
                Schema::rename('tbl_funding_type', 'tbl_finance_funding_type');
            }
        });
    }

}
