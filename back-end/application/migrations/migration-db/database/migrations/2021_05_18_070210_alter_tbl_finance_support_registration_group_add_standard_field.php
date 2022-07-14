<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceSupportRegistrationGroupAddStandardField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_support_registration_group', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_support_registration_group', 'is_standard')) {
                $table->unsignedTinyInteger('is_standard')->default(0)->comment('0 - No, 1 - Yes');
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
        Schema::table('tbl_finance_support_registration_group', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_support_registration_group', 'is_standard')) {
                $table->dropColumn('is_standard');
            }
        });
    }
}
