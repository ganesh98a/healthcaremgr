<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class SafetyChecklistCategories extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "safety_checklist_categories.json");
        $queryData = (array) json_decode($json, true);
        
        foreach($queryData['safety_checklist_categories'] as $cat) {
            DB::table('tbl_staff_saftey_checklist_categories')->insert([
                'category_name' => $cat['name'],
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0
            ]);
            $cat_id = DB::getPdo()->lastInsertId();
            foreach($cat['items'] as $item) {
                DB::table('tbl_staff_saftey_checklist_items')->insert([
                    'category_id' => $cat_id,
                    'item_name' => $item,
                    'created_at' => date('Y-m-d h:i:s'),
                    'updated_at' => date('Y-m-d h:i:s'),
                    'archive' => 0
                ]);
            }
        }
    }
}
