<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblReferenceAsAddColumnKeyName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_references', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_references', 'key_name')) {
                $table->string('key_name',200)->after('display_name')->nullable();
            }
        });

        $seeder_1 = new ReferenceDataSeeder();
        $seeder_1->run();

        $seeder = new ReferenceJobRequiredDocs();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_references', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_references', 'key_name')) {
                $table->dropColumn('key_name');
            }
        });
    }
}
