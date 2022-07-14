<?php

use Illuminate\Database\Seeder;

class ServiceAreaSWA extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $map =  [
                    'CSS DSW Residential' => 
                    [
                        ['CSS DSW Residential Vic', 'Disability'],
                        ['CSS DSW Residential Vic', 'DDSO'],
                        ['CSS DSW Residential Qld', 'Disability']
                    ],
                    'CSS DSW Day Programs' => 
                    [
                        ['CSS DSW Day Programs Vic', 'Disability'],
                        ['CSS DSW Day Programs Vic', 'Day Services']
                    ],
                    'CSS NDIS' => 
                    [
                        ['CSS NDIS Vic', 'Disability'],
                        ['CSS NDIS Vic', 'Welfare']
                    ],
                    'CSS CYF Driving/Other' => 
                    [
                        ['CSS CYF Driving/Other', 'Welfare']
                    ],
                    'CSS CYF Residential' => 
                    [
                        ['CSS CYF Residential', 'Welfare']
                    ],
                    'NDIS Individual Services' => 
                    [
                        ['NDIS DSW', 'Disability'],
                        ['NDIS DSW', 'Welfare'],
                        ['NDIS Job Ready', 'Disability']
                    ]
                ];
                $sas = array_flip(DB::table('tbl_finance_service_area')->select(['id', 'title'])->pluck('title', 'id')->toArray());
                $swas = array_flip(DB::table('tbl_finance_support_worker_area')->select(['id', 'title'])->pluck('title', 'id')->toArray());
                $ccs = array_flip(DB::table('tbl_finance_cost_code')->select(['id', 'title'])->pluck('title', 'id')->toArray());
                
                foreach($map as $sa => $arr) {
                    $sa_id = $sas[$sa];
                    foreach($arr as $v) {
                        $swa_id = $swas[$v[0]];
                        $cc_id = $ccs[$v[1]];
                        DB::table('tbl_finance_service_area_swa_mapping')->insert([
                            'service_area_id' => $sa_id,
                            'swa_id' => $swa_id,
                            'cost_code_id' => $cc_id,
                            'created_at' => date('Y-m-d h:i:s'),
                            'updated_at' => date('Y-m-d h:i:s'),
                            'archive' => 0
                        ]);
                    }
                }
    }
}
