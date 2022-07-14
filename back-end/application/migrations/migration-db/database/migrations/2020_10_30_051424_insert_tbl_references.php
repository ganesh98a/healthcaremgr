<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertTblReferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_references', function (Blueprint $table) {
            $res = DB::select("select * from tbl_references where code = '007'");
            if(!$res) {
                DB::statement("INSERT INTO `tbl_references`(type, code, display_name, key_name, start_date) values(1,'007','Oncall - Website Contact','oncall_website_contact','2020-10-30')");
            }
            else {
                DB::statement("UPDATE `tbl_references` SET type = 1, code = '007', display_name = 'Oncall - Website Contact', key_name = 'oncall_website_contact', start_date = '2020-10-30' WHERE id = ".$res[0]->id);
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
        
    }
}
