<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmStaffDepartmentAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_staff_department_allocations')) {
            Schema::create('tbl_crm_staff_department_allocations', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('admin_id');
                $table->unsignedInteger('allocated_department');
                $table->unsignedInteger('status');
                $table->timestamp('created')->useCurrent();
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
        Schema::dropIfExists('tbl_crm_staff_department_allocations');
    }
}
