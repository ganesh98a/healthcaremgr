<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class AlterTblParticipantMasterTblShiftTblSaAddOcpIdColumn extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
           
            Schema::table('tbl_participants_master', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_participants_master', 'ocp_id')) {
                    $table->unsignedInteger('ocp_id')->comment('ocp_id_for_migration')->after('id');
                }
    
            });
            Schema::table('tbl_shift', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_shift', 'ocp_id')) {
                    $table->unsignedInteger('ocp_id')->comment('ocp_id_for_migration')->after('id');
                }
    
            });
            Schema::table('tbl_service_agreement', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_service_agreement', 'ocp_id')) {
                    $table->unsignedInteger('ocp_id')->comment('ocp_id_for_migration')->after('id');
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
            Schema::table('tbl_participants_master', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_participants_master', 'ocp_id')) {
                     $table->dropColumn('ocp_id');
                }
            });
            Schema::table('tbl_shift', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_shift', 'ocp_id')) {
                     $table->dropColumn('ocp_id');
                }
            });
            Schema::table('tbl_service_agreement', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_service_agreement', 'ocp_id')) {
                     $table->dropColumn('ocp_id');
                }
            });
        }
    }
    