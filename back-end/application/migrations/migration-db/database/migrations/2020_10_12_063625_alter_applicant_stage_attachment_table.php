<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterApplicantStageAttachmentTable extends Migration
{
  
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'file_path')) {
                
                $table->text('file_path')->nullable()->after('attachment');

            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'aws_object_uri')) {
                
                $table->text('aws_object_uri')->nullable()->after('reference_number');           

            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'aws_response')) {
                
                $table->text('aws_response')->nullable()->after('reference_number');

            }
           
            if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'aws_uploaded_flag')) {
                
                $table->unsignedInteger('aws_uploaded_flag')->default(0)->nullable()->after('reference_number')->comment('1 - Yes / 0 - No');
            
            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'file_type')) {
                
                $table->string('file_type', 255)->nullable();
            
            }

            if (!Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'file_size')) {
                
                $table->string('file_size', 255)->nullable();
            
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
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {            
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'file_path')) {
                $table->dropColumn('file_path');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'aws_object_uri')) {
                $table->dropColumn('aws_object_uri');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'aws_response')) {
                $table->dropColumn('aws_response');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'aws_uploaded_flag')) {
                $table->dropColumn('aws_uploaded_flag');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'file_type')) {
                $table->dropColumn('file_type');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'file_size')) {
                $table->dropColumn('file_size');
            }
        });
    }
}
