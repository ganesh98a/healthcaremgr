<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_login')) {
            Schema::create('tbl_member_login', function(Blueprint $table)
                {
                    $table->unsignedInteger('memberId');
                    $table->text('token');
                    $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                    $table->dateTime('updated')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_member_login');
    }
}
