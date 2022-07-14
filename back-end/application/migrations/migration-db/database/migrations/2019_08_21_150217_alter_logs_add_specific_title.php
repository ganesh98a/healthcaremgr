<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLogsAddSpecificTitle extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_logs', function (Blueprint $table) {
            if (Schema::hasTable('tbl_logs')) {
                $table->text('specific_title')->after('title')->nullable()->comment('for specfic module like recruitment and crm');
                $table->unsignedInteger('module')->comment('primary key of tbl_module_title')->change();
                $table->unsignedInteger('sub_module')->comment('primary key of tbl_module_title')->change();
                $table->unsignedInteger('created_type')->comment('1 - admin / 2 - participant portal')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_logs', function (Blueprint $table) {
            if (Schema::hasTable('tbl_logs')) {
                $table->dropColumn('specific_title');
                $table->unsignedInteger('module')->comment('1- Admin/ 2- Participant / 3 - Member/ 4 - Schedule / 5 - FSM / 6 - House / 7 - Organization / 8 - Imail / 9 - Recruitment / 10 - CRMAdmin')->change();
                $table->unsignedInteger('sub_module')->comment('if schedule (1 - Shift / 2 - Roster) , if Imail (1 - Externam mail/ 2 - Internal Mail)')->change();
                $table->unsignedInteger('created_type')->comment('1 - admin / 2 - participant')->change();
            }
        });
    }

}
