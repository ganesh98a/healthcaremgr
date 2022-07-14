<?php

use Illuminate\Database\Seeder;

class UpdateSafetyListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is there more than one level on the property?'");
        if (!empty($row)) {
            $cat_id1 = $row[0]->category_id;
            $id1 = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('category_id', $cat_id1)
                ->update([
                    "sort_order" => 3
                ]);
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id1)
                ->update([
                    "sort_order" => 1
                ]);
            DB::table('tbl_staff_saftey_checklist_items')->insert([
                'item_name' => 'Is the property compliant with accessibility standards',
                'category_id' => $cat_id1,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0,
                'sort_order' => 2
            ]);
        }

        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'If I need them, are there working lifts or accessible stairways?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Are there working lifts or accessible stairways?'
                ]);
        }

        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Are any entrances/exits to the property obstructed in any way, or could they pose a risk to safety? Eg, broken/uneven stairs'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Are any of the entrances or exits obstructed? Eg, broken/uneven stairs'
                ]);
        }

        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is there difficulty with mobile phone reception?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Is there reliable telecommunication reception at the property?'
                ]);
        }

        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is there a safe place to park my car?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Is there off street parking available?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Are there any barriers or restrictions to parking close to the property?'");
        if (empty($row)) {
            DB::table('tbl_staff_saftey_checklist_items')->insert([
                'item_name' => 'Are there any barriers or restrictions to parking close to the property?',
                'category_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0,
                'sort_order' => 4
            ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is there adequate external and street lighting?'");
        if (empty($row)) {
            DB::table('tbl_staff_saftey_checklist_items')->insert([
                'item_name' => 'Is there adequate external and street lighting?',
                'category_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0,
                'sort_order' => 5
            ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is there a safe place to park my car?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Is there off street parking available?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Are there any barriers or restrictions to parking close to the property?'");
        if (empty($row)) {
            DB::table('tbl_staff_saftey_checklist_items')->insert([
                'item_name' => 'Are there any barriers or restrictions to parking close to the property?',
                'category_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0,
                'sort_order' => 6
            ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is there adequate external and street lighting?'");
        if (empty($row)) {
            DB::table('tbl_staff_saftey_checklist_items')->insert([
                'item_name' => 'Is there adequate external and street lighting?',
                'category_id' => 1,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0,
                'sort_order' => 7
            ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is there adequate lighting/ventilation inside?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Is there adequate lighting to undertake necessary tasks?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Are smoke detectors present and maintained?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Are smoke detectors present?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Do you have any pets/animals on the property?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Are there pets on the property?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Can the animals be put in a room or outside during home visits, if needs be?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Can they be separated from service delivery if required?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Are there any barriers or obstacles to freely move around the property?'");
        if (empty($row)) {
            DB::table('tbl_staff_saftey_checklist_items')->insert([
                'item_name' => 'Are there any barriers or obstacles to freely move around the property?',
                'category_id' => 2,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0,
                'sort_order' => 4
            ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is the property in an identified Bush Fire Risk area?'");
        if (empty($row)) {
            DB::table('tbl_staff_saftey_checklist_items')->insert([
                'item_name' => 'Is the property in an identified Bush Fire Risk area?',
                'category_id' => 2,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0,
                'sort_order' => 5
            ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is the person able to self evacuate in an emergency?'");
        if (empty($row)) {
            DB::table('tbl_staff_saftey_checklist_items')->insert([
                'item_name' => 'Is the person able to self evacuate in an emergency?',
                'category_id' => 2,
                'created_at' => date('Y-m-d h:i:s'),
                'updated_at' => date('Y-m-d h:i:s'),
                'archive' => 0,
                'sort_order' => 5
            ]);
        }

        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Do you or other occupants in the house smoke inside?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Does the person or other occupants smoke inside the property?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Are there any other occupants or visitors who may be present during home visits?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Are there any other occupants or visitors who may be present during service delivery?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Do you or any other occupants have a known history of physical violence?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Do any of the other occupants or visitors at the property pose a risk to staff?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Does the client or any other occupants have use of firearms in the past?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Are there any firearms stored on the property?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'If so, is this equipment safe and maintained?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Are any aids or equipment used in good working order and well maintained?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is manual handling required?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Is adequate space available for manual handling equipment?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Are there any biological hazards (could the staff come into contact with any body fluids)?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Is there any risk posed to staff by bodily fluid?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Is there appropriate PPE available for personal care?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Is there PPE available at the property?'
                ]);
        }
        $row = DB::select("Select id, category_id from tbl_staff_saftey_checklist_items where item_name = 'Any other risks/issues to report?'");
        if (!empty($row)) {
            $id = $row[0]->id;
            DB::table('tbl_staff_saftey_checklist_items')
                ->where('id', $id)
                ->update([
                    "item_name" => 'Are there any other issues that would present a risk?'
                ]);
        }
    }
}
