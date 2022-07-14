<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblReferenceDataTypeAsMakeKeyNameUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_reference_data_type')) {
            DB::unprepared("ALTER TABLE tbl_reference_data_type CHANGE key_name key_name varchar(200) COLLATE 'latin1_swedish_ci' NOT NULL AFTER title");
            DB::unprepared("ALTER TABLE tbl_reference_data_type ADD UNIQUE key_name (key_name)");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
