<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;
    
    class AlterTblShiftAddShiftactionColumns extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
           
           
            Schema::table('tbl_shift', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_shift', 'shift_start_action_datetime')) {
                    $table->dateTime('shift_start_action_datetime')->nullable();
                }
                if (!Schema::hasColumn('tbl_shift', 'shift_end_action_datetime')) {
                    $table->dateTime('shift_end_action_datetime')->nullable();
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
                if (Schema::hasColumn('tbl_shift', 'shift_start_action_datetime')) {
                     $table->dropColumn('shift_start_action_datetime');
                }
                if (Schema::hasColumn('tbl_shift', 'shift_end_action_datetime')) {
                    $table->dropColumn('shift_end_action_datetime');
               }
            });
           
        }
    }
    