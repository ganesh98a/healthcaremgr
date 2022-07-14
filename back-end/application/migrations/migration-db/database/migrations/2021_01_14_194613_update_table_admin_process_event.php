<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableAdminProcessEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_admin_process_event', function (Blueprint $table) {
            $table->text('conditions')->nullable()->comment('json encoded string for conditions');
            $table->string('condition_logic', 255)->nullable()->comment('logical expression for conditions');
            $table->text('expression_inputs')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_admin_process_event', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_admin_process_event', 'conditions')) {
                $table->dropColumn('conditions');
            }
            if (Schema::hasColumn('tbl_admin_process_event', 'condition_logic')) {
                $table->dropColumn('condition_logic');
            }
            if (Schema::hasColumn('tbl_admin_process_event', 'expression_inputs')) {
                $table->dropColumn('expression_inputs');
            }
        });
    }
}
