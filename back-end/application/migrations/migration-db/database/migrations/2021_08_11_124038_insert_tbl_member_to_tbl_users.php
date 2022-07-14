<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertTblMemberToTblUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if(Schema::hasTable('tbl_users')) {
            DB::table('tbl_users')->truncate();
        }

        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'uuid')) {
                DB::table('tbl_member')
                ->update(["uuid" => NULL]);
            }
        });

        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'uuid')) {
                DB::table('tbl_person')
                ->update(["uuid" => NULL]);
            }
        });

        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant', 'uuid')) {
                DB::table('tbl_recruitment_applicant')
                ->update(["uuid" => NULL]);
            }
        });

        $list_of_member = DB::select("SELECT m.id, m.username,m.password,m.otp, m.created_by, m.created,m.status,m.department, m.person_id, m.archive from tbl_member as m  order by m.id ASC");
        
        foreach($list_of_member as $value) { 
            $user_type = 1;
            $password = $value->password;
            if($value->department==2){
                $user_type = 2;
                if(!empty($value->person_id)){
                    $get_person_password = DB::select("SELECT password FROM `tbl_person` where id = ".$value->person_id);
                    if(!empty($get_person_password)){
                        $password = $get_person_password[0]->password;
                    }                 
                }   
            }
               
                $created_at = $value->created ? date('Y-m-d h:i:s', strtotime($value->created)) : NULL;
                        $create_member = array(
                            "id"=>$value->id,
                            "username" => $value->username,
                            "password" => $password,
                            'user_type' => $user_type,
                            "created_at" => $created_at,
                            "created_by" => $value->created_by,
                            "password_token" => NULL,
                            "status"=> $value->status,
                            "archive"=>$value->archive
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
