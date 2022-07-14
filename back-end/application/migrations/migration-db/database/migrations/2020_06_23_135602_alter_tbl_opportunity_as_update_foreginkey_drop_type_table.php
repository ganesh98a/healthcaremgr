<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityAsUpdateForeginkeyDropTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            $table->dropForeign('tbl_opportunity_opportunity_type_foreign');
            $table->unsignedInteger('opportunity_type')->comment("tbl_references.id")->change();
        });
        Schema::dropIfExists('tbl_opportunity_type');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            //
        });
    }
}
