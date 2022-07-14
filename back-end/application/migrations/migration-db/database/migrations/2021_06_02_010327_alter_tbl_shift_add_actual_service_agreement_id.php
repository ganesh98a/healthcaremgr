<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddActualServiceAgreementId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'actual_sa_id')) {
                $table->unsignedInteger('actual_sa_id')->nullable()->after("scheduled_reimbursement")->comment('tbl_service_agreement.id');
                $table->foreign('actual_sa_id')->references('id')->on('tbl_service_agreement')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tbl_shift', 'actual_docusign_id')) {
                $table->unsignedInteger('actual_docusign_id')->nullable()->after("actual_sa_id")->comment('tbl_service_agreement_attachment.id');
                $table->foreign('actual_docusign_id')->references('id')->on('tbl_service_agreement_attachment')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tbl_shift', 'actual_support_type')) {
                $table->unsignedInteger('actual_support_type')->nullable()->after("actual_docusign_id")->comment('tbl_finance_support_type.id');
                $table->foreign('actual_support_type')->references('id')->on('tbl_finance_support_type')->onUpdate('cascade')->onDelete('cascade');
            }

            if (Schema::hasColumn('tbl_shift', 'service_agreement_id')) {
                $table->renameColumn('service_agreement_id', 'scheduled_sa_id');
            }
            if (Schema::hasColumn('tbl_shift', 'docusign_id')) {
                $table->renameColumn('docusign_id', 'scheduled_docusign_id');
            }
            if (Schema::hasColumn('tbl_shift', 'support_type')) {
                $table->dropForeign(['support_type']);
                $table->renameColumn('support_type', 'scheduled_support_type');
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
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'actual_sa_id')) {
                $table->dropForeign(['actual_sa_id']);
                $table->dropColumn('actual_sa_id');
            }
            if (Schema::hasColumn('tbl_shift', 'actual_docusign_id')) {
                $table->dropForeign(['actual_docusign_id']);
                $table->dropColumn('actual_docusign_id');
            }
            if (Schema::hasColumn('tbl_shift', 'actual_support_type')) {
                $table->dropForeign(['actual_support_type']);
                $table->dropColumn('actual_support_type');
            }

            if (Schema::hasColumn('tbl_shift', 'scheduled_sa_id')) {
                $table->renameColumn('scheduled_sa_id', 'service_agreement_id');
            }
            if (Schema::hasColumn('tbl_shift', 'scheduled_docusign_id')) {
                $table->renameColumn('scheduled_docusign_id', 'docusign_id');
            }
            if (Schema::hasColumn('tbl_shift', 'scheduled_support_type')) {
                $table->renameColumn('scheduled_support_type', 'support_type');
            }
        });
    }
}
