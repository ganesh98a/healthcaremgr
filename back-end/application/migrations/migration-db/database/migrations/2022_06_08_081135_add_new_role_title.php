<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewRoleTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $seeder = new AddNewRoleSeeder();
        $seeder->run();

        //Add weight field to display the roles in proper order
        Schema::table('tbl_role', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_role', 'weight')) {
                $table->integer('weight')->unsigned()->nullable()->comment('Weight for display order')->after('status');
            }           
        });

        $row = DB::select("Select id from tbl_role order by id asc");
        if (!empty($row)) {
            foreach ($row as $key => $obj) {
                if($obj->id == 19 ) {
                    $weight = 0;
                } else {
                    $weight = $key + 1; 
                } 

                DB::table('tbl_role')
                ->where('id', $obj->id)
                ->update([
                    "weight" => $weight
                ]);
            }
                
        }

        //Create Default BU if OGA not available in BU 
        $bucount = DB::select("Select count(*) as count from tbl_business_units");
        
        if(!empty($bucount[0]->count) == 0) {
            $data['business_unit_name'] = 'Oncall Group Victoria';
            $data['region_id'] = '7';
            $data['status'] = 1;
            $data['owner_id'] = 1;
        
            DB::table('tbl_business_units')->insert($data);
        }
        //Set default OGA BU id to all the users
        if (!Schema::hasTable('tbl_user_business_unit')) {
            Schema::create('tbl_user_business_unit', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('bu_id')->unsigned()->comment('id of tbl_business_units');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
                $table->unsignedInteger('user_id')->unsigned()->comment('id of tbl_users');
                $table->foreign('user_id')->references('id')->on('tbl_users');
                $table->unsignedInteger('created_by')->comment('the user who initiated the field change, or zero if initiated by the system');
                $table->foreign('created_by')->references('id')->on('tbl_users'); 
                $table->dateTimeTz('created_at')->nullable(); 
            });
        }
        
        $row = DB::select("Select id from tbl_users order by id asc");

        if(!empty($row)){
            foreach($row as $data) {
                DB::table('tbl_user_business_unit')->insert([
                    'user_id' => $data->id,
                    'bu_id' => 1,
                    'created_by' => 1,
                    'created_at' => date('y-m-d h:i:s')
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
        Schema::table('tbl_role', function (Blueprint $table) {
            //
        });
    }
}
