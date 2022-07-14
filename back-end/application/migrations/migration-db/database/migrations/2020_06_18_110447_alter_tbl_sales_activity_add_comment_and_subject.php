<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblSalesActivityAddCommentAndSubject extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_sales_activity', function (Blueprint $table) {

            if (!Schema::hasColumn('tbl_sales_activity', 'subject')) {
                $table->string('subject')->nullable()->after("taskId");
            }

            if (Schema::hasColumn('tbl_sales_activity', 'description')) {
                $table->renameColumn('description', "comment")->change();
            }
            
            if (Schema::hasColumn('tbl_sales_activity', 'taskId')) {
                $table->unsignedInteger('taskId')->nullable()->change();
            }
            
            if (!Schema::hasColumn('tbl_sales_activity', 'contactId')) {
                $table->unsignedBigInteger('contactId')->comment('tbl_person.id')->nullable()->after("id");
            }

            if (!Schema::hasColumn('tbl_sales_activity', 'related_to')) {
                $table->unsignedInteger('related_to')->comment('primary key of as per relation_type (tbl_opportunity|tbl_leads|tbl_service_agreement|tbl_need_assessment|tbl_crm_risk_assessment)')->nullable()->after("contactId");
            }

            if (!Schema::hasColumn('tbl_sales_activity', 'related_type')) {
                $table->unsignedInteger('related_type')->comment('1-opportunity/2-lead/3- service agreement/4-needs assessment/5-Risk assessment')->nullable()->after("related_to");;
            }
        });

        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_activity', 'comment')) {
                $table->text("comment")->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_activity', 'comment')) {
                $table->renameColumn('comment', "description")->change();
            }

            if (!Schema::hasColumn('tbl_sales_activity', 'subject')) {
                $table->dropColumn('subject');
            }
            
            if (Schema::hasColumn('tbl_sales_activity', 'contactId')) {
                $table->dropColumn('contactId');
            }

            if (Schema::hasColumn('tbl_sales_activity', 'related_to')) {
                $table->dropColumn('related_to');
            }

            if (Schema::hasColumn('tbl_sales_activity', 'related_type')) {
                $table->dropColumn('related_type');
            }
        });
    }

}
