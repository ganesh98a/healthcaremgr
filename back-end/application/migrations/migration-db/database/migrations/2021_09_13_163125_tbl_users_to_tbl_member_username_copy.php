<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblUsersToTblMemberUsernameCopy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $list_of_member = DB::select("SELECT m.id as member_id, u.id, u.user_type, u.username FROM `tbl_users` as u join tbl_member as m on m.uuid=u.id where u.user_type=1");
        
        foreach($list_of_member as $value) { 
            // fetch uuid in member table
            if(!empty($value)){
                DB::table('tbl_member')
                ->where(["uuid"=>$value->id])
                ->update(["username" => $value->username]);
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
