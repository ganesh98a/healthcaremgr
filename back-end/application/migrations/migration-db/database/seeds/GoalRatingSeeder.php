<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;


class GoalRatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$json = File::get(Config::get('constants.JSON_FILE_PATH')."tbl_goal_rating.json");
    	$queryData = (array) json_decode($json, true);
    	foreach ($queryData as $obj) {
    		DB::table('tbl_goal_rating')->updateOrInsert(['id' => $obj['id']],
    			$obj);
    	}
    
    }
}
