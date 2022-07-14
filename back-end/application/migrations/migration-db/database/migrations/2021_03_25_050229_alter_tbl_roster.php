<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRoster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_roster', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_roster','booked_by')) {
                $table->renameColumn('booked_by', 'account_type');
            }
            if (Schema::hasColumn('tbl_roster','userId')) {
                $table->renameColumn('userId', 'account_id');
            }
            if (!Schema::hasColumn('tbl_roster', 'owner_id')){
                $table->unsignedInteger('owner_id')->nullable()->after('id')->comment('reference of tbl_member.id');
            }
            if (!Schema::hasColumn('tbl_roster', 'roster_type')){
                $table->unsignedInteger('roster_type')->nullable()->after('owner_id')->comment('reference of tbl_reference.id');
            }
            if (!Schema::hasColumn('tbl_roster', 'contact_id')){
                $table->unsignedInteger('contact_id')->nullable()->after('roster_type')->comment('reference of tbl_person.id');
            }
            if (!Schema::hasColumn('tbl_roster', 'funding_type')){
                $table->unsignedInteger('funding_type')->nullable()->after('contact_id')->comment('reference of tbl_reference.id');
            }
            if (!Schema::hasColumn('tbl_roster', 'end_date_option')){
                $table->unsignedInteger('end_date_option')->nullable()->after('contact_id')->comment('1 - End of 6 weeks / 2 - Custom Date');
            }
            if (!Schema::hasColumn('tbl_roster', 'roster_no')){
                $table->text('roster_no')->nullable()->after('id');
            }

            if (!Schema::hasColumn('tbl_roster', 'archive')){
                $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
            }
            if (!Schema::hasColumn('tbl_roster', 'updated_by')){
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }
            if (Schema::hasColumn('tbl_roster', 'status')){
                $table->unsignedInteger('status')->nullable()->default(1)->comment('1 - Active / 2 - InActive')->change();
            }
            if (!Schema::hasColumn('tbl_roster', 'stage')){
                $table->unsignedInteger('stage')->nullable()->default(1)->after('status')->comment('1 - Open / 2 - Finalize / 3 - In progress / 4 - Completed');
            }
            # Delete unwanted columns
            if (Schema::hasColumn('tbl_roster', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('tbl_roster', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('tbl_roster', 'booked_by')) {
                // $table->dropColumn('booked_by');
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
        Schema::table('tbl_roster', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_roster', 'roster_no')) {
                $table->dropColumn('roster_no');
            }
            if (Schema::hasColumn('tbl_roster', 'owner_id')) {
                $table->dropColumn('owner_id');
            }
            if (Schema::hasColumn('tbl_roster', 'roster_type')) {
                $table->dropColumn('roster_type');
            }
            if (Schema::hasColumn('tbl_roster', 'contact_id')) {
                $table->dropColumn('contact_id');
            }
            if (Schema::hasColumn('tbl_roster', 'funding_type')) {
                $table->dropColumn('funding_type');
            }
            if (Schema::hasColumn('tbl_roster', 'end_date_option')) {
                $table->dropColumn('end_date_option');
            }
            if (Schema::hasColumn('tbl_roster', 'archive')) {
                $table->dropColumn('archive');
            }
            if (Schema::hasColumn('tbl_roster', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('tbl_roster', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('tbl_roster','account_type')) {
                $table->renameColumn('account_type', 'booked_by');
            }
            if (Schema::hasColumn('tbl_roster','account_id')) {
                $table->renameColumn('account_id', 'userId');
            }
        });
    }
}
