<?php

use Illuminate\Database\Seeder;

class ServiceArea extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sas = [
            'CSS DSW Residential', 'CSS DSW Day Programs', 'CSS NDIS', 'CSS CYF Driving/Other',
            'CSS CYF Residential', 'NDIS Individual Services'
        ];
        foreach ($sas as $sa) {
            DB::table('tbl_finance_service_area')->insert([
                'title' => $sa,
                'created_at' => date('Y-m-d h:i:s')
            ]);
        }
    }
}
