<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameActivityEmailRecipeientNameToActivityRecipient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('tbl_sales_activity_email_recipient', 'tbl_sales_activity_recipient');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('tbl_sales_activity_recipient', 'tbl_sales_activity_email_recipient');
    }
}
