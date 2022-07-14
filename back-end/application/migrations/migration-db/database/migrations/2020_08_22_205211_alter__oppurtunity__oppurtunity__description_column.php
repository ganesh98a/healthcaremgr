<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOppurtunityOppurtunityDescriptionColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_opportunity', 'oppurtunity_description')) {
                $table->text('oppurtunity_description')->nullable()->after('amount');
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
