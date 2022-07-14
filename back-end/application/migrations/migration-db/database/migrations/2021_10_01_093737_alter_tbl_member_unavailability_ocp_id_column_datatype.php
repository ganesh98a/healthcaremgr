<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberUnavailabilityOcpIdColumnDatatype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
       
        Schema::table('tbl_member_unavailability', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_unavailability', 'ocp_id')) {
                $table->bigInteger('ocp_id')->nullable()->change();
               
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
        Schema::table('tbl_member_unavailability', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_unavailability', 'ocp_id')) {
                 $table->unsignedInteger('ocp_id')->nullable()->change();
            }
        });
       
    }
}
