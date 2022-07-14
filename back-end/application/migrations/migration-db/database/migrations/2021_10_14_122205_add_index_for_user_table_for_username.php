<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexForUserTableForUsername extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tbl_users', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'uuid')){
                // add index and change data type
                $table->string('password',255)->nullable()->change();
                $table->index(['username', 'password']);
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
        //
    }
}
