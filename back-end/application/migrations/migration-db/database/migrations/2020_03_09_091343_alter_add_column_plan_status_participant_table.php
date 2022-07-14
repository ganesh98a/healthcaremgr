<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnPlanStatusParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participant', function (Blueprint $table) {
          if (!Schema::hasColumn('tbl_participant', 'plan_status')) {
            $table->unsignedTinyInteger('plan_status')->nullable()->comment('1-modified, 2-renewed');
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
        Schema::table('tbl_participant', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_participant', 'plan_status')) {
            $table->dropColumn('plan_status');
          }
        });
    }
}
