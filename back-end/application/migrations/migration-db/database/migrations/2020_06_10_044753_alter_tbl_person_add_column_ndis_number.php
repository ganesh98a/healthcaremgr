<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddColumnNdisNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_person')) {
            Schema::table('tbl_person', function(Blueprint $table) {
                if ( ! Schema::hasColumn('tbl_person', 'ndis_number')) {
                    $table->string('ndis_number', 20)->nullable();
                }
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
        Schema::table('tbl_person', function(Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'ndis_number')) {
                $table->dropColumn('ndis_number');
            }
        });
    }
}
