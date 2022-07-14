<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblSaPaymentsAddSelfManagedContactId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sa_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sa_payments', 'self_managed_contact_id')) {
                $table->unsignedSmallInteger('self_managed_contact_id')->nullable()->after('self_type_contact_name');
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
        Schema::table('tbl_sa_payments', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sa_payments', 'self_managed_contact_id')) {
                $table->dropColumn('self_managed_contact_id');
            }
        });
    }
}
