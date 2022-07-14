<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceStaffAddUserIdAndTrigger extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_staff', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_staff', 'userId')) {
                $table->string('userId', 100)->after('adminId')->nullable()->default(null);
            }
        });

        DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_staff_before_insert_add_userId`');
        DB::unprepared("CREATE TRIGGER `tbl_finance_staff_before_insert_add_userId` BEFORE INSERT ON `tbl_finance_staff` FOR EACH ROW
        IF NEW.userId IS NULL or NEW.userId='' THEN
          SET NEW.userId = (SELECT CONCAT('F',((SELECT id FROM tbl_finance_staff ORDER BY id DESC LIMIT 1) + 1)+10000));
          END IF;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_staff_before_insert_add_userId`');

        Schema::table('tbl_finance_staff', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_staff', 'userId')) {
                $table->dropColumn('userId');
            }
        });
    }

}
