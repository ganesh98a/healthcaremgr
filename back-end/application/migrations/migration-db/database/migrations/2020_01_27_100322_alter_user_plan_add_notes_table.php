<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserPlanAddNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       if (Schema::hasTable('tbl_user_plan')) {
            Schema::table('tbl_user_plan', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_user_plan','notes')) {                    
					$table->string('notes', 500)->nullable()->after('funding_type');
                }
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
        if (Schema::hasTable('tbl_user_plan')) {
            Schema::table('tbl_user_plan', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_user_plan','notes')) {
                    $table->dropColumn('notes');
                }
            });
          }
    }
}
