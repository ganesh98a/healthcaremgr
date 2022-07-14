<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_department')) {
            Schema::create('tbl_crm_department', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name',100);
                $table->string('hcmgr_id',20);
                $table->timestamp('created')->useCurrent();
                $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data(delete)');

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
        Schema::dropIfExists('tbl_crm_department');
    }
}
