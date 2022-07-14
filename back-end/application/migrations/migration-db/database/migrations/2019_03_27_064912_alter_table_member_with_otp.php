<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMemberWithOtp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (Schema::hasTable('tbl_member')) {
			Schema::table('tbl_member', function ($table) {
				$table->string('otp',20)->after('dwes_confirm');
				$table->datetime('otp_expire_time')->after('otp');
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
		if (Schema::hasTable('tbl_member')) {
			 Schema::table('tbl_member', function ($table) {
				$table->dropColumn('otp');
				$table->dropColumn('otp_expire_time');
			});
		}
    }
}
