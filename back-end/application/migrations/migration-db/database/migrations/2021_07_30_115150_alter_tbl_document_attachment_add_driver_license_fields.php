<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblDocumentAttachmentAddDriverLicenseFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_document_attachment')) {
            Schema::table('tbl_document_attachment', function (Blueprint $table) {
                $table->tinyInteger('license_type')->nullable()->comment('1=> International, 2=> Probationary, 3=> Interstate');
                $table->tinyInteger('issuing_state')->nullable()->comment('tbl_state.id');
                $table->date('vic_conversion_date',)->nullable()->comment('Convert to Victorian license on/before');
                $table->tinyInteger('applicant_specific')->nullable()->comment('if document is related to applicant');
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
        Schema::table('tbl_document_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_document_attachment', 'license_type')) {
                $table->dropColumn('license_type');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'issuing_state')) {
                $table->dropColumn('issuing_state');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'vic_conversion_date')) {
                $table->dropColumn('vic_conversion_date');
            }
            if (Schema::hasColumn('tbl_document_attachment', 'applicant_specific')) {
                $table->dropColumn('applicant_specific');
            }
        });
    }
}
