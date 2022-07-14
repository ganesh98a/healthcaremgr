<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBuIdTemplateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sms_template', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sms_template', 'bu_id')) {
                $table->unsignedInteger('bu_id')->nullable()->comment('business unit id')->after('id');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
            }
            
        });
        $row = DB::select("Select id from tbl_sms_template order by id asc");

        if(!empty($row)){
            foreach($row as $data) {
               
                DB::table('tbl_sms_template')
                ->where('id', $data->id)
                ->update([
                    "bu_id" => 1
                ]);
            }
        }
        Schema::table('tbl_email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_email_templates', 'bu_id')) {
                $table->unsignedInteger('bu_id')->nullable()->comment('business unit id')->after('id');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
            }
        
        });
        $row = DB::select("Select id from tbl_email_templates order by id asc");

        if(!empty($row)){
            foreach($row as $data) {
               
                DB::table('tbl_email_templates')
                ->where('id', $data->id)
                ->update([
                    "bu_id" => 1
                ]);
            }
        }
        Schema::table('tbl_recruitment_oa_template', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_oa_template', 'bu_id')) {
                $table->unsignedInteger('bu_id')->nullable()->comment('business unit id')->after('id');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
            }
           
        });
        $row = DB::select("Select id from tbl_recruitment_oa_template order by id asc");

        if(!empty($row)){
            foreach($row as $data) {
               
                DB::table('tbl_recruitment_oa_template')
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
