<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBuIdTblRecruitmentForm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_form', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_form', 'bu_id')) {
                $table->unsignedInteger('bu_id')->nullable()->comment('business unit id')->after('title');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
            }            
        });
        $row = DB::select("Select id from tbl_recruitment_form order by id asc");

            if(!empty($row)){
                foreach($row as $data) {                   
                    DB::table('tbl_recruitment_form')
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
