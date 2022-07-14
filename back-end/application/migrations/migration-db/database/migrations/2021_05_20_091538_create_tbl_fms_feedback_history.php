<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFmsFeedbackHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_feedback_history')) {
            Schema::create('tbl_fms_feedback_history', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('feedback_id')->unsigned();
                $table->unsignedInteger('created_by')->comment('the user who initiated the field change, or zero if initiated by the system');
                $table->foreign('created_by')->references('id')->on('tbl_member');          // do not cascade
                $table->dateTimeTz('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));    // not nullable
            });
        }

        if (!Schema::hasTable('tbl_fms_feedback_field_history')) {
            Schema::create('tbl_fms_feedback_field_history', function (Blueprint $table) {
                $fields = [
                    'feedback_id','companyId','event_date','assigned_to','shiftId','initiated_by','initiated_type','escalate_to_incident',
                    'Initiator_first_name','Initiator_last_name','Initiator_email','Initiator_phone','description','against_type','against_category','against_by',
                    'against_first_name','against_last_name','against_email','against_phone','address',
                    'status','alert_type','feedback_type','fms_type','completed_date','categoryId','department_id','created_by','created','updated','updated_by'
                ];

                $table->bigIncrements('id');
                $table->bigInteger('history_id')->unsigned()->comment('the assosciated fms feedback history item');
                $table->foreign('history_id')->references('id')->on('tbl_fms_feedback_history')->onDelete('cascade');
                $table->integer('feedback_id')->unsigned();
                $table->enum('field', $fields);
                $table->mediumText('value')->comment('current field value');
                $table->mediumText('prev_val')->comment('previous field value')->nullable();
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
        Schema::dropIfExists('tbl_fms_feedback_history');
        Schema::dropIfExists('tbl_fms_feedback_field_history');
    }
}
