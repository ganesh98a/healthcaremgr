<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblBusinessUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        if (!Schema::hasTable('tbl_business_units')) {
            Schema::create('tbl_business_units', function (Blueprint $table) {
                $table->increments('id');
                $table->string('business_unit_name', 255)->nullable()->comment('Business unit name');
                $table->unsignedInteger('region_id')->nullable()->comment('id of tbl_state');
                $table->foreign('region_id')->references('id')->on('tbl_state')->onUpdate('cascade')->onDelete('cascade');
                $table->text('notes')->nullable()->comment('Description about business units');
                $table->bigInteger('status')->unsigned()->nullable();
                $table->unsignedInteger('owner_id')->nullable()->comment('reference of tbl_member.uuid');
                $table->unsignedInteger('archive')->default('0');
                $table->dateTime('created')->nullable();
				$table->unsignedInteger('created_by')->nullable();
				$table->foreign('created_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
				$table->dateTime('updated')->nullable();
				$table->unsignedInteger('updated_by')->nullable()->comment('reference id of tbl_member.id');
                $table->foreign('updated_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            });
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_business_units');
    }
}
