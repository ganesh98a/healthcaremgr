<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblSalesActivityAddParticipantType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sales_activity', 'participant_type')) {
                $table->unsignedInteger('participant_type')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_activity', 'participant_type')) {
                $table->dropColumn('participant_type');
            }
        });
    }
}
