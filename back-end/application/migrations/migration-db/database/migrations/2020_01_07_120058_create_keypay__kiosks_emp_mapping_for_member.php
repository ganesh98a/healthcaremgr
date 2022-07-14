<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeypayKiosksEmpMappingForMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_keypay_kiosks_emp_mapping_for_member')) {
            Schema::create('tbl_keypay_kiosks_emp_mapping_for_member', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('member_id')->comment('tbl_member');
                $table->string('keypay_emp_id', 255);
                $table->unsignedInteger('keypay_auth_id')->nullable()->comment("tbl_keypay_auth_details auto increment id");
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
        Schema::dropIfExists('tbl_keypay_kiosks_emp_mapping_for_member');
    }
}
