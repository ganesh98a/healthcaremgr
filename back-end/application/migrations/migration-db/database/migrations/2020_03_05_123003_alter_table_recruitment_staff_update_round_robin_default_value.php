<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentStaffUpdateRoundRobinDefaultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_recruitment_staff')){
            Schema::table('tbl_recruitment_staff', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_staff','round_robin_status')){
                    $table->unsignedSmallInteger('round_robin_status')->default('0')->nullable()->comment('round robin Management status 1 for on and 0 for off')->change();
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
        if(Schema::hasTable('tbl_recruitment_staff')){
            Schema::table('tbl_recruitment_staff', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_staff','round_robin_status')){
                    $table->unsignedSmallInteger('round_robin_status')->default('1')->nullable()->comment('round robin Management status 1 for on and 0 for off')->change();
                }
            });
        }
    }
}
