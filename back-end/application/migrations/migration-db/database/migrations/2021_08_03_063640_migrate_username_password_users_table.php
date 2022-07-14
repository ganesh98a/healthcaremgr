<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateUsernamePasswordUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $list_of_member = DB::select("SELECT m.id, m.username,m.password,m.otp, m.created_by, m.created,m.status, (select me.email from tbl_member_email as me where me.memberId = m.id AND me.primary_email = 1 AND me.archive = 0) as email, (select mp.phone from tbl_member_phone as mp where mp.memberId = m.id AND mp.primary_phone = 1 AND mp.archive = 0) as phone FROM `tbl_member` as `m` INNER JOIN `tbl_department` as `d` ON `d`.`id` = `m`.`department` AND `d`.`short_code` = 'internal_staff' ORDER BY `m`.`created` ASC");
        
        foreach($list_of_member as $value) { 
            $check_user_exists = [];
            if($value->status==1){
                $check_user_exists = DB::select("SELECT * FROM `tbl_users` where username = '".$value->email."' and user_type=1");
                $created_at = $value->created ? date('Y-m-d h:i:s', strtotime($value->created)) : NULL;
                    if(empty($check_user_exists)){
                        
                        $create_member = array(
                            "username" => $value->email,
                            "password" => $value->password,
                            'user_type' => 1,
                            "created_at" => $created_at,
                            "created_by" => $value->created_by,
                            "password_token" => $value->otp,
                        ); 


                        DB::table('tbl_users')                  
                        ->insert($create_member); 

                        $created_user_id = DB::getPdo()->lastInsertId();

                        // fetch uuid in member table
                        if(!empty($created_user_id)){
                            DB::table('tbl_member')
                            ->where("id",$value->id)
                            ->update(["uuid" => $created_user_id]);
                        }
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
