<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityAddAccountType extends Migration {
	public function listTableForeignKeys($table) {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }
	
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
			$list = $this->listTableForeignKeys("tbl_opportunity");
			
            if (!Schema::hasColumn('tbl_opportunity', 'account_type')) {
                $table->unsignedSmallInteger('account_type')->nullable()->comment("1-Person/2-organisation")->after("neeed_support_plan");
            }
			
			if(Schema::hasColumn('tbl_opportunity', 'account_person')){
				$table->bigInteger('account_person')->unsigned()->comment("tbl_person.id / tbl_organisaition.id")->change();
			}
			
			if (in_array("tbl_opportunity_account_person_foreign", $list)) {
                $table->dropForeign('tbl_opportunity_account_person_foreign');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity', 'account_type')) {
                $table->dropColumn('account_type');
            }
        });
    }

}
