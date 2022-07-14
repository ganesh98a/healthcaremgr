<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdateSafetyListQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_staff_saftey_checklist_items', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_staff_saftey_checklist_items', 'sort_order')) {
                $seeder = new UpdateSafetyListSeeder();
                $seeder->run();
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
        $sql = "DELETE FROM `tbl_staff_saftey_checklist_items`";
        DB::statement($sql);
        $sql = "INSERT INTO `tbl_staff_saftey_checklist_items` (`id`, `category_id`, `item_name`, `archive`, `created_at`, `updated_at`) VALUES
        (1, 1, 'Is there more than one level on the property?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (2, 1, 'If I need them, are there working lifts or accessible stairways?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (3, 1, 'Are any entrances/exits to the property obstructed in any way, or could they pose a risk to safety? Eg, broken/uneven stairs', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (4, 1, 'Is there difficulty with mobile phone reception?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (5, 1, 'Is there a safe place to park my car?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (6, 1, 'Is there a working landline (home phone) on the property?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (7, 1, 'Is there more than 1 entry or exit point to the property?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (8, 2, 'Is there adequate lighting/ventilation inside?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (9, 2, 'Are there any trip or slip hazards?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (10, 2, 'Are there any fire hazards?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (11, 2, 'Are smoke detectors present and maintained?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (12, 2, 'Do you have all the required equipment to work safely within your home?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (13, 2, 'Do you have any pets/animals on the property?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (14, 2, 'Can the animals be put in a room or outside during home visits, if needs be?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (15, 2, 'Where does the pet go to the toilet?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (16, 3, 'Do you or other occupants in the house smoke inside?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (17, 3, 'Do you or any other occupants have a known history of physical violence?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (18, 3, 'Are there any other occupants or visitors who may be present during home visits?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (19, 3, 'Does the client or any other occupants have use of firearms in the past?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (20, 4, 'Is manual handling required?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (21, 4, 'Is any equipment in working order?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (22, 4, 'If medication is administered by ONCALL,?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (23, 4, 'If so,has an ONCALL medication treatment sheet been completed?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (24, 4, 'Is medication stored safely?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (25, 4, 'Any other risks/issues to report?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (26, 4, 'Do you use communication aides?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (27, 4, 'Do you use any mobility aides?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (28, 4, 'If so, is this equipment safe and maintained?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (29, 4, 'Are there any biological hazards (could the staff come into contact with any body fluids)?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56'),
        (30, 4, 'Is there appropriate PPE available for personal care?', 0, '2021-04-28 18:38:56', '2021-04-28 18:38:56');";
        DB::statement($sql);
    }
}
