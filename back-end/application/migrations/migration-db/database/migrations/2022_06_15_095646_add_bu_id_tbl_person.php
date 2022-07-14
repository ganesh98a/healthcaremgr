<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBuIdTblPerson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'bu_id')) {
                $table->unsignedInteger('bu_id')->nullable()->comment('business unit id')->after('id');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
            }

        });

        $row = DB::select("Select id from tbl_person order by id asc");

            if(!empty($row)){
                foreach($row as $data) {
                   
                    DB::table('tbl_person')
                    ->where('id', $data->id)
                    ->update([
                        "bu_id" => 1
                    ]);
                }
            }
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
