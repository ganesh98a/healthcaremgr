<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;

class AlterTblOpportunitySetAmountDecimal132 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_opportunity')) {
            Schema::table('tbl_opportunity', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_opportunity', 'amount')) {
                    $table->decimal('amount', 13, 2)->default(0.00)->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_opportunity')) {
            Schema::table('tbl_opportunity', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_opportunity', 'amount')) {
                    if (!Type::hasType('double')) {
                        Type::addType('double', FloatType::class);
                    }
                    $table->double('amount',10,2)->change();
                }
            });
        }
    }
}
