<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertRecordToLeadStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_lead_status', function (Blueprint $table) {
                $obj = array(
                    "id"=>"5",
                    "name"=>"Accomodation Waitlist",
                    "key_name"=>"accomodation",
                    "order_ref"=>"80",
                    "archive"=>"0"
                );
            
                DB::table('tbl_lead_status')->updateOrInsert(['id' => $obj['id']], $obj);
        
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_lead_status', function (Blueprint $table) {
            DB::delete('delete from tbl_lead_status where id=5');
    
    });
}
}
