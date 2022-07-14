<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertMrMsMrsIntoTblReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::table('tbl_references')->updateOrInsert(
            ['type' => 3, 'code' => 'mr'],
            ['display_name' => 'Mr', 'created' => '2020-05-25 15:24:33', 'archive' => 0]
        );
        
        \DB::table('tbl_references')->updateOrInsert(
            ['type' => 3, 'code' => 'ms'],
            ['display_name' => 'Ms', 'created' => '2020-05-25 15:24:33', 'archive' => 0]
        );

        \DB::table('tbl_references')->updateOrInsert(
            ['type' => 3, 'code' => 'mrs'],
            ['display_name' => 'Mrs', 'created' => '2020-05-25 15:24:33', 'archive' => 0]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // this migration is not reversible
    }
}
