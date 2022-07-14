<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'person_id')) {
                $table->unsignedBigInteger('person_id')->nullable()->comment('tbl_person.id')->after('companyId');
                $table->foreign('person_id')->references('id')->on('tbl_person')->onDelete(DB::raw('SET NULL'));
            }

            if (!Schema::hasColumn('tbl_member', 'fullname')) {
                $table->mediumText('fullname')->nullable()->after('person_id');
            }

            if (!Schema::hasColumn('tbl_member', 'hours_per_week')) {
                $table->decimal('hours_per_week',9)->nullable()->after('dob');
            }

            if (!Schema::hasColumn('tbl_member', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }

            if (!Schema::hasColumn('tbl_member', 'updated_date')) {
                $table->dateTime('updated_date')->nullable();
            }

            if (!Schema::hasColumn('tbl_member', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'person_id')) {
                $table->dropForeign(['person_id']);
                $table->dropColumn('person_id');
            }
            if (Schema::hasColumn('tbl_member', 'fullname')) {
                $table->dropColumn('fullname');
            }
            if (Schema::hasColumn('tbl_member', 'hours_per_week')) {
                $table->dropColumn('hours_per_week');
            }
            if (Schema::hasColumn('tbl_member', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('tbl_member', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('tbl_member', 'updated_date')) {
                $table->dropColumn('updated_date');
            }
        });
    }
}
