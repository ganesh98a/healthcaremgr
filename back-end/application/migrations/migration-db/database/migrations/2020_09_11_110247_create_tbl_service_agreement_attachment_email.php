<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblServiceAgreementAttachmentEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_service_agreement_attachment_email', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_agreement_attachment_id')->comment('tbl_service_agreement_attachment_id.id');
            $table->foreign('service_agreement_attachment_id','sa_attachment_id_foreign_key')->references('id')->on('tbl_service_agreement_attachment_id')->onDelete('CASCADE');
            $table->text('subject',255);
            $table->text('email_content')->nullable();
            $table->unsignedInteger('cc_email_flag')->nullable()->comment('0 - No / 1 - Yes');
            $table->text('cc_email',255)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_service_agreement_attachment_email', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement_attachment_email', 'service_agreement_attachment_id')) {
                $table->dropForeign(['sa_attachment_id_foreign_key']);
            }
        });
        Schema::dropIfExists('tbl_service_agreement_attachment_email');
    }
}