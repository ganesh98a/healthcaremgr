<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMember extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_member', function (Blueprint $table) {
            $table->string('username', 20)->after('preferredname');
            $table->text('password')->after('username');
            $table->string('position', 100)->after('password');
            $table->text('otp')->change();
            $table->unsignedInteger('department')->after('position');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_member', function (Blueprint $table) {
            $table->dropColumn('username');
            $table->dropColumn('password');
            $table->dropColumn('position');
            $table->dropColumn('department');
            $table->string('otp', 20)->change();
        });
    }

}
