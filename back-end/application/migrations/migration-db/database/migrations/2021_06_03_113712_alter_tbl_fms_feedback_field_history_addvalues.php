<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsFeedbackFieldHistoryAddvalues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        if (Schema::hasTable('tbl_fms_feedback_field_history')) {       
            \DB::statement("ALTER TABLE `tbl_fms_feedback_field_history` CHANGE `field` `field` 
            ENUM('feedback_id','companyId','event_date','assigned_to','shiftId','initiated_by','initiated_type','escalate_to_incident',
            'Initiator_first_name','Initiator_last_name','Initiator_email','Initiator_phone','description','against_type','against_category','against_by',
            'against_first_name','against_last_name','against_email','against_phone','address',
            'status','alert_type','feedback_type','fms_type','completed_date','categoryId','department_id','created_by','created','updated','updated_by',
            'notes_reason','notify_email') 
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_fms_feedback_field_history', function (Blueprint $table) {
            //
        });
    }
}
