<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAdminProcessEventAddRecipientType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_admin_process_event')) {
            Schema::table('tbl_admin_process_event', function (Blueprint $table) {
                $table->string('recipient_type')->after('recipient')->nullable();
            });
            Schema::table('tbl_admin_process_event', function (Blueprint $table) {
                $table->text('recipient')->change();
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
        if (Schema::hasTable('tbl_admin_process_event') && Schema::hasColumn('tbl_admin_process_event', 'recipient_type')) {
            Schema::table('tbl_admin_process_event', function (Blueprint $table) {
                $table->dropColumn('recipient_type');
            });
        }
    }
}
