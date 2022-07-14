<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FileDownloadValidation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_file_download_validation', function (Blueprint $table) {
            
            $table->string('token', 30)->nullable()->comment('Temporary one time token its helps to validate the token on download');
            $table->timestamp('created')->useCurrent();         
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_file_download_validation');
    }
}
