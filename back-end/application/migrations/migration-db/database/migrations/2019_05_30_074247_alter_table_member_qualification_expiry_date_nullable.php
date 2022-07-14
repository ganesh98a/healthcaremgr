<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMemberQualificationExpiryDateNullable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_member_qualification')) {
            Schema::table('tbl_member_qualification', function (Blueprint $table) {
                $table->date('expiry')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_member_qualification')) {
            Schema::table('tbl_member_qualification', function (Blueprint $table) {
                $table->date('expiry')->nullable(false)->change();
            });
        }
    }

}
