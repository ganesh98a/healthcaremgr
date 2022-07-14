<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftRequirementsAndRemomveTblShiftParticipantRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if(Schema::hasTable('tbl_shift_requirements')){
            Schema::dropIfExists('tbl_shift_participant_requirements');
            Schema::table('tbl_shift_requirements', function (Blueprint $table) {
                if( !Schema::hasColumn('tbl_shift_requirements','id')){
                    $table->increments('id')->first();
                }
                if( Schema::hasColumn('tbl_shift_requirements','requirementId')){
                    $table->unsignedInteger('requirementId')->comment('tbl_participant_genral auto increment id')->nullable()->change();
                }
                if( !Schema::hasColumn('tbl_shift_requirements','requirement_type')){
                    $table->unsignedSmallInteger('requirement_type')->default(2)->comment('1 for mobility AND 2 for assistance')->nullable();
                }
                if( !Schema::hasColumn('tbl_shift_requirements','requirement_other')){
                    $table->string('requirement_other')->nullable()->comment('other text only for site and house');
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
        if( Schema::hasTable('tbl_shift_requirements')){
            Schema::table('tbl_shift_requirements', function (Blueprint $table) {
                if( Schema::hasColumn('tbl_shift_requirements','id')){
                    $table->dropColumn('id');
                }
                if( Schema::hasColumn('tbl_shift_requirements','requirement_type')){
                    $table->dropColumn('requirement_type');
                }
                if( !Schema::hasColumn('tbl_shift_requirements','requirement_other')){
                    $table->dropColumn('requirement_other');
                }
            });
        }
    }
}
