<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldNeedAssessmentMobility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_need_assessment_mobility')) {
            Schema::table('tbl_need_assessment_mobility', function (Blueprint $table) {

                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'can_mobilize')) {
                    $table->unsignedSmallInteger('can_mobilize')->comment("0- No, 1- Yes")
                        ->after('need_assessment_id');
                }
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'short_distances')) {
                    $table->unsignedSmallInteger('short_distances')->comment("0- No, 1- Yes")
                        ->after('can_mobilize');
                }
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'long_distances')) {
                    $table->unsignedSmallInteger('long_distances')->comment("0- No, 1- Yes")
                        ->after('short_distances');
                }
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'up_down_stairs')) {
                    $table->unsignedSmallInteger('up_down_stairs')->comment("0- No, 1- Yes")
                        ->after('long_distances');
                }
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'uneven_surfaces')) {
                    $table->unsignedSmallInteger('uneven_surfaces')->comment("0- No, 1- Yes")
                        ->after('up_down_stairs');
                }

                //Adding comments
                if (Schema::hasColumn('tbl_need_assessment_mobility', 'inout_bed')) {
                    $table->unsignedSmallInteger('inout_bed')->comment("1- Not applicable, 2- with assistance, 3- with supervision, 4- independant, 5- With Aids and Equipment")->change();
                }
                if (Schema::hasColumn('tbl_need_assessment_mobility', 'inout_shower')) {
                    $table->unsignedSmallInteger('inout_shower')->comment("1- Not applicable, 2- with assistance, 3- with supervision, 4- independant, 5- With Aids and Equipment")->change();
                }
                if (Schema::hasColumn('tbl_need_assessment_mobility', 'onoff_toilet')) {
                    $table->unsignedSmallInteger('onoff_toilet')->comment("1- Not applicable, 2- with assistance, 3- with supervision, 4- independant, 5- With Aids and Equipment")->change();
                }
                if (Schema::hasColumn('tbl_need_assessment_mobility', 'inout_chair')) {
                    $table->unsignedSmallInteger('inout_chair')->comment("1- Not applicable, 2- with assistance, 3- with supervision, 4- independant, 5- With Aids and Equipment")->change();
                }
                if (Schema::hasColumn('tbl_need_assessment_mobility', 'inout_vehicle'))
                {
                    $table->unsignedSmallInteger('inout_vehicle')->comment("1- Not applicable, 2- with assistance, 3- with supervision, 4- independant, 5- With Aids and Equipment")->change();
                }

                //Equipment Used
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'inout_bed_equipment_used')) {
                    $table->text('inout_bed_equipment_used')->nullable()->comment('inout_bed_equipment_used details')->after('inout_vehicle');
                }
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'inout_shower_equipment_used')) {
                    $table->text('inout_shower_equipment_used')->nullable()->comment('inout_shower_equipment_used details')->after('inout_bed_equipment_used');
                }
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'onoff_toilet_equipment_used')) {
                    $table->text('onoff_toilet_equipment_used')->nullable()->comment('onoff_toilet_equipment_used details')->after('inout_shower_equipment_used');
                }
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'inout_chair_equipment_used')) {
                    $table->text('inout_chair_equipment_used')->nullable()->comment('inout_chair_equipment_used details')->after('onoff_toilet_equipment_used');
                }
                if (!Schema::hasColumn('tbl_need_assessment_mobility', 'inout_vehicle_equipment_used'))
                {
                    $table->text('inout_vehicle_equipment_used')->nullable()->comment('inout_vehicle_equipment_used details')->after('inout_chair_equipment_used');
                }

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
