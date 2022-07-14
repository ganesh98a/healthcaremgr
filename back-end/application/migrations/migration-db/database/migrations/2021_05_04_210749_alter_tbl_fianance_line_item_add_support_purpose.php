<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFiananceLineItemAddSupportPurpose extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            $table->unsignedInteger('support_purpose')->nullable()->comment('primay key of tbl_finance_support_purpose')->after('support_category');
            $table->unsignedInteger('support_type')->nullable()->comment('primay key of tbl_finance_support_type')->after('support_purpose');
            $table->mediumText('needs')->nullable()->after('support_type');
            $table->unsignedInteger('public_holiday')->nullable()->comment('0 -Not/1- Yes')->change();
            $table->unsignedInteger('member_ratio')->nullable()->change();
            $table->unsignedInteger('participant_ratio')->nullable()->change();
            // $table->double('national_price_limit', 14, 2)->nullable()->change();
            // $table->double('national_very_price_limit', 14, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_line_item', 'support_purpose_id')) {
                $table->dropColumn('support_purpose_id');
            }
            if (Schema::hasColumn('tbl_finance_line_item', 'support_type_id')) {
                $table->dropColumn('support_type_id');
            }
            if (Schema::hasColumn('tbl_finance_line_item', 'needs')) {
                $table->dropColumn('needs');
            }
            $table->unsignedInteger('public_holiday')->nullable(false)->comment('0 -Not/1- Yes')->change();
            $table->unsignedInteger('member_ratio')->nullable(false)->change();
            $table->unsignedInteger('participant_ratio')->nullable(false)->change();
            // $table->double('national_price_limit', 14, 2)->nullable(false)->change();
            // $table->double('national_very_price_limit', 14, 2)->nullable(false)->change();
        });
    }
}
