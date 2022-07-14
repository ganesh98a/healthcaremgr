<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_approval')) {
            Schema::create('tbl_approval', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('userId');
                $table->unsignedSmallInteger('user_type')->comment('1- Member, 2- Participant');
                $table->string('approval_area',200)->comment('like UpdateProfile,Care Requirement');
                $table->text('approval_content')->comment('JSON');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->timestamp('approval_date')->default('0000-00-00 00:00:00');
                $table->unsignedInteger('approved_by');
                $table->unsignedTinyInteger('status')->default(0)->comment('0 Not Approved(Request by Member/Participant) ,1 Approved ,2 Cancel');
                $table->unsignedTinyInteger('pin')->comment('0- not / 1 - yes');
                $table->timestamp('updated')->useCurrent();
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
        Schema::dropIfExists('tbl_approval');
    }
}
