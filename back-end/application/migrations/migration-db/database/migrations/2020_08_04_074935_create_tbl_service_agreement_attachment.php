<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblServiceAgreementAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_service_agreement_attachment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type')->comment("type 0 - Not Selected, 1 - Consent, 2 - Service Agreement");
            $table->unsignedInteger('to');
            $table->string('to_select',255)->nullable();
            $table->unsignedInteger('account_id');
            $table->string('related',255)->nullable();
            $table->unsignedInteger('service_agreement_id')->comment("tbl_service_agreement.id");
            $table->foreign('service_agreement_id')->references('id')->on('tbl_service_agreement')->onDelete('CASCADE');
            $table->string('envelope_id',255)->nullable()->comment('docusign api unique id');
            $table->unsignedSmallInteger('envelop_status')->default('0')->comment('0- mean not signed yet,1-sigend');
            $table->string('unsigned_file',255)->nullable()->comment('file path');
            $table->string('signed_file',255)->nullable()->comment('file path');
            $table->unsignedSmallInteger('signed_status')->default('0')->comment('0- mean not signed yet,1-sigend');
            $table->dateTime('signed_date')->default('0000-00-00 00:00:00');
            $table->dateTime('send_date')->default('0000-00-00 00:00:00');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->unsignedTinyInteger('archive')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_service_agreement_attachment');
    }
}
