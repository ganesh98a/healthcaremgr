<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberWorkAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_work_area')) {
            Schema::create('tbl_member_work_area', function(Blueprint $table)
                {
                    $table->increments('id', true);
                    $table->unsignedInteger('companyId');
                    $table->unsignedInteger('memberId');
                    $table->unsignedTinyInteger('work_area')->comment('1 = Client & NDIS Services, 2 = Out Of Home Care 3 = Disability Accommodation 4 = Casual Staff Service -Disability 5 = Casual Staff Service -Welfare');
                    $table->unsignedTinyInteger('work_status')->comment('1 = Yes, 2 = Yes, Not Preffered, 3 = No, Inexperienced, 4 = No, ONCALL Request, 5 = Registered');
                    $table->dateTime('created');
                    $table->unsignedTinyInteger('archive')->comment('1- Delete');
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
        Schema::dropIfExists('tbl_member_work_area');
    }
}
