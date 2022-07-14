<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePasswordInTblLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $logs = DB::select("SELECT l.id , l.description FROM `tbl_logs` as l  ORDER BY `l`.`id` ASC");                  

        foreach($logs as $val) {
            $tmp  = (object)$val;
            if(!empty($tmp ->description)){
                $tmp = $tmp ->description;
                $json = json_decode($tmp, true);
                if(isset($json['password']) && !empty($json['password'])){
                    $json['password'] = '*******';
                        DB::table('tbl_logs')
                        ->where('id',$val->id)
                        ->update([
                            "description" => json_encode($json)
                    ]);
                }
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
