<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOutOfOfficeMessageAddRenameColumnAndCreatedBy extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_out_of_office_message', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_out_of_office_message', 'created_by')) {
                $table->unsignedInteger('created_by')->comment('primary key for tbl_member')->default(0)->after("additional_message");
            }

            if (Schema::hasColumn('tbl_out_of_office_message', 'adminId') && !Schema::hasColumn('tbl_out_of_office_message', 'create_for')) {
                $table->renameColumn('adminId', 'create_for');
            }

            if (Schema::hasColumn('tbl_out_of_office_message', 'concactId') && !Schema::hasColumn('tbl_out_of_office_message', 'replace_by')) {
                $table->renameColumn('concactId', 'replace_by');
            }

            if (Schema::hasColumn('tbl_out_of_office_message', 'default_message')) {
                $table->dropColumn('default_message');
            }
        });

        Schema::table('tbl_out_of_office_message', function (Blueprint $table) {
            $table->unsignedInteger('replace_by')->nullable()->comment('primary key for tbl_member')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_out_of_office_message', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_out_of_office_message', 'created_by')) {
                $table->dropColumn('created_by');
            }

            if (!Schema::hasColumn('tbl_out_of_office_message', 'adminId') && Schema::hasColumn('tbl_out_of_office_message', 'create_for')) {
                $table->renameColumn('create_for', "adminId");
            }

            if (!Schema::hasColumn('tbl_out_of_office_message', 'concactId') && Schema::hasColumn('tbl_out_of_office_message', 'replace_by')) {
                $table->renameColumn('replace_by', "concactId");
            }
        });
    }

}
