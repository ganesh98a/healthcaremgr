<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblFinanceSupportRegistrationGroupDataIsStandard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $is_standard_group = [ 107, 120 ];
        $im_group = implode(',', $is_standard_group);
        # update query
        DB::statement("UPDATE `tbl_finance_support_registration_group` SET `is_standard` = 1 WHERE `prefix` IN (".$im_group.")");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $is_standard_group = [ 107, 120 ];
        $im_group = implode(',', $is_standard_group);
        # update query
        DB::statement("UPDATE `tbl_finance_support_registration_group` SET `is_standard` = 0 WHERE `prefix` IN (".$im_group.")");
    }
}
