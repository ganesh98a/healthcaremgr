<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmStageAddStageKeyOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
		Schema::table('tbl_crm_stage', function (Blueprint $table) {
			if (!Schema::hasColumn('tbl_crm_stage', 'stage_key')) {
                $table->string('stage_key', 50)->after('name');
            }
			if (!Schema::hasColumn('tbl_crm_stage', 'stage_order')) {
                $table->unsignedInteger('stage_order')->default(0)->after('stage_key');
            }
			if (!Schema::hasColumn('tbl_crm_stage', 'last_stage')) {
                $table->unsignedInteger('last_stage')->default(0)->after('stage_order');
            }
		});
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_stage', function (Blueprint $table) {
            //
        });
    }
}
