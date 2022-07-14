<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceStaffTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_staff', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('adminId')->comment('primary key of tbl_member');
            $table->unsignedSmallInteger('status')->comment('0 - Inactive/ 1 - Active');
            $table->unsignedInteger('approval_permission')->comment('0 - No/1 - Yes use for added as finance user');
            $table->unsignedInteger('access_permission')->comment('0 - Not all/1 - All/ --> only for infomation purpose not for check permission');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedSmallInteger('archive')->comment('0 - Not/ 1 - Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_finance_staff');
    }

}
