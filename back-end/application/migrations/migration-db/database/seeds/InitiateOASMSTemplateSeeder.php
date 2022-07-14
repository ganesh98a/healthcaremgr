<?php

use Illuminate\Database\Seeder;

class InitiateOASMSTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tbl_sms_template')->insert([
            'name' =>'Online Assessment Initiated',
            'used_to_initiate_oa' => 1,
            'short_description' => 'Online Assessment link sent',
            'content' => 'Hello. We have sent you an Online Assessment as part of your job application with us. Please check it has been received, including your junk folders. Recruitment Team at ONCALL Group Australia',
            'folder' => 'public',
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => date('Y-m-d h:i:s'),
            'updated_at' => date('Y-m-d h:i:s'),
            'archive' => 0
        ]);
    }

    public function undoRun()
    {
        DB::table('tbl_sms_template')->where(['used_to_initiate_oa' => 1])->delete();
    }
}
