<?php

use Illuminate\Database\Seeder;

class SupportWorkerArea extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $swas = ['CSS DSW Residential Vic', 'CSS DSW Residential Qld', 'CSS DSW Day Programs Vic', 'CSS NDIS Vic', 'CSS CYF Driving/Other', 'CSS CYF Residential', 'NDIS DSW', 'NDIS Job Ready'];
        foreach ($swas as $swa) {
            DB::table('tbl_finance_support_worker_area')->insert([
                'title' => $swa,
                'created_at' => date('Y-m-d h:i:s')
            ]);
        }
    }
}
