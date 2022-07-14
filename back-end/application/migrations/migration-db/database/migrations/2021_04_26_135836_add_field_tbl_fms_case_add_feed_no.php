<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldTblFmsCaseAddFeedNo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_case', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_fms_case', 'feedback_id')) {
                $table->string('feedback_id', 255)->comment('Feedback ID Example FK000000001')
                ->after('id');
            }
        });

         # Update the Existing Feedback id if exists
         $res = DB::select("select id from tbl_fms_case");

         if(!empty($res)) {
             foreach($res as $row) {
                 $feedback_id = "FK".sprintf("%09d", $row->id);
                 DB::statement("UPDATE `tbl_fms_case` SET `feedback_id` = '{$feedback_id}' where id = {$row->id}");
             }
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
