<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFundingTypeAddColumnKeyName extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_funding_type', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_funding_type', 'key_name')) {
                $table->string('key_name', 100)->after("name");
            }
        });
        
        $obj = new FinanceFundingType();
        $obj->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_funding_type', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_funding_type', 'key_name')) {
                $table->dropColumn('key_name');
            }
        });
    }

}
