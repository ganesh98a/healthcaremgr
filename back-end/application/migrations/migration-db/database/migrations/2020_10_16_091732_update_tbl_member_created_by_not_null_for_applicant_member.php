<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblMemberCreatedByNotNullForApplicantMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
    // Updating created by as not null for applicant member    
      DB::table('tbl_member')
          ->where('applicant_id','!=',0)
          ->where('created_by',NULL)
          ->update([
              "created_by" => 1
      ]);
      
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
