<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;


class ParticipantGenralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH')."tbl_participant_genral.json");
        $queryData = (array) json_decode($json, true);
        DB::statement("TRUNCATE TABLE tbl_participant_genral");
        foreach ($queryData as $obj) {
            DB::table('tbl_participant_genral')->updateOrInsert(['id' => $obj['id']],$obj);
        }
    }
}
