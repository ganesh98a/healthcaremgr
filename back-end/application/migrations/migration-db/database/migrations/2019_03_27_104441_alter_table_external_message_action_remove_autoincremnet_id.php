<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableExternalMessageActionRemoveAutoincremnetId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_external_message_action')) {
            Schema::table('tbl_external_message_action', function (Blueprint $table) {
                $table->unsignedInteger('messageId',false)->change();
                $table->dropPrimary(['messageId']);
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
        if (Schema::hasTable('tbl_external_message_action')) {
            Schema::table('tbl_external_message_action', function (Blueprint $table) {
                $table->primary('messageId','messageId');
                $table->unsignedInteger('messageId')->change();
            });
        }
    }
}
