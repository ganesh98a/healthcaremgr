<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentStaffDepartmentAllocationsAddAreaType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_staff_department_allocations', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_staff_department_allocations')) {
                $table->unsignedInteger('area_type')->after('allocated_department')->comment('1 - allocation/ 2 - preffered');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_staff_department_allocations', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_staff_department_allocations')) {
                $table->dropColumn('area_type');
            }
        });
    }

}
