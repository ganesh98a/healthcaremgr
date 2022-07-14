<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMemberLogin extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_member_login', function (Blueprint $table) {
            $table->increments('id')->before('memberId');
            $table->string('ip_address', 20)->after('memberId');
            $table->text('pin')->after('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_member_login', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropColumn('ip_address');
            $table->dropColumn('pin');
        });
    }

}
