<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentStaffDepartmentAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_staff_department_allocations')) {
            Schema::create('tbl_recruitment_staff_department_allocations', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('adminId');
                $table->unsignedInteger('allocated_department');
                $table->unsignedInteger('status')->comment('1- Active, 0- Inactive');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_recruitment_staff_department_allocations');
    }
}
