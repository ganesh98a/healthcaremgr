<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCmsContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_cms_content', function (Blueprint $table) {
            $table->increments('id');
            $table->text('url',255)->nullable()->comment('S3 Json url of the exported CMS content');
            $table->decimal('version',30)->nullable()->comment('Incremental version');
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_cms_content');
    }
}
