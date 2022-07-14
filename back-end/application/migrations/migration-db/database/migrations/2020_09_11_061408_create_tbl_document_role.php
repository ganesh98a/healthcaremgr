<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDocumentRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_document_role')) {
            Schema::create('tbl_document_role', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('document_id')->comment('tbl_documents.id');
                $table->foreign('document_id')->references('id')->on('tbl_documents')->onDelete('CASCADE');
                $table->unsignedInteger('role_id')->comment("tbl_member_role.id");
                $table->foreign('role_id')->references('id')->on('tbl_member_role')->onDelete('CASCADE');

                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->unsignedInteger('mandatory')->default('0')->comment('0 = not mandatory, 1 = mandatory');

                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('updated')->nullable();

                $table->unsignedInteger('created_by');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');

                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');

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
        if (Schema::hasTable('tbl_document_role')) {
            Schema::table('tbl_document_role', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_document_role', 'document_id')) {
                    $table->dropForeign(['document_id']);
                }
                if (Schema::hasColumn('tbl_document_role', 'role_id')) {
                    $table->dropForeign(['role_id']);
                }
                if (Schema::hasColumn('tbl_document_role', 'created_by')) {
                    $table->dropForeign(['created_by']);
                }
                if (Schema::hasColumn('tbl_document_role', 'updated_by')) {
                    $table->dropForeign(['updated_by']);
                }
            });
        }
        Schema::dropIfExists('tbl_document_role');
    }
}
